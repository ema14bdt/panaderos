<?php

require_once 'bootstrap.php';
admin_require_login();

header('Content-Type: application/json; charset=utf-8');

function admin_detect_binary($name) {
    $candidates = array(
        '/usr/bin/' . $name,
        '/usr/local/bin/' . $name,
        '/bin/' . $name
    );
    foreach ($candidates as $cand) {
        if (@is_file($cand) && @is_executable($cand)) {
            return $cand;
        }
    }
    if (function_exists('shell_exec')) {
        $which = @shell_exec('which ' . escapeshellarg($name) . ' 2>/dev/null');
        $which = trim((string) $which);
        if ($which !== '' && @is_file($which) && @is_executable($which)) {
            return $which;
        }
    }
    return null;
}

function admin_read_mp4_duration($filepath) {
    $fp = @fopen($filepath, 'rb');
    if (!$fp) return null;
    $header = fread($fp, 262144);
    if (strlen($header) < 16 || strpos(substr($header, 0, 32), 'ftyp') === false) {
        fclose($fp);
        return null;
    }
    $pos = strpos($header, 'mvhd');
    if ($pos === false) {
        $filesize = @filesize($filepath);
        if ($filesize > 262144) {
            fseek($fp, max(0, $filesize - 524288));
            $tail = fread($fp, 524288);
            $pos = strpos($tail, 'mvhd');
            if ($pos !== false) {
                $header = $tail;
            }
        }
    }
    fclose($fp);
    if ($pos === false) return null;
    $version = ord($header[$pos + 4]);
    if ($version === 0 && strlen($header) >= $pos + 24) {
        $unpacked = unpack('Ntimescale/Nduration', substr($header, $pos + 16, 8));
        if (!empty($unpacked['timescale'])) {
            return (float) ($unpacked['duration'] / $unpacked['timescale']);
        }
    } elseif ($version === 1 && strlen($header) >= $pos + 36) {
        $tsData = unpack('N', substr($header, $pos + 24, 4));
        $timescale = $tsData ? $tsData[1] : 0;
        $durData = unpack('Nhigh/Nlow', substr($header, $pos + 28, 8));
        if ($durData && $timescale > 0) {
            $duration = ($durData['high'] * 4294967296.0) + $durData['low'];
            return (float) ($duration / $timescale);
        }
    }
    return null;
}

if (!isset($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(array('success' => false, 'error' => 'Método no permitido.'));
    exit;
}

try {
    admin_verify_csrf();

    $isVideo = isset($_FILES['video']) && is_array($_FILES['video']);
    $isAudio = isset($_FILES['audio']) && is_array($_FILES['audio']);
    if (!$isVideo && !$isAudio && (!isset($_FILES['image']) || !is_array($_FILES['image']))) {
        throw new InvalidArgumentException('No se recibió ningún archivo.');
    }

    $file = $isVideo ? $_FILES['video'] : ($isAudio ? $_FILES['audio'] : $_FILES['image']);
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $uploadErrors = array(
            UPLOAD_ERR_INI_SIZE   => 'El archivo excede el tamaño máximo permitido por el servidor.',
            UPLOAD_ERR_FORM_SIZE  => 'El archivo excede el tamaño máximo permitido en el formulario.',
            UPLOAD_ERR_PARTIAL    => 'El archivo se subió solo parcialmente.',
            UPLOAD_ERR_NO_FILE    => 'No se seleccionó ningún archivo.',
            UPLOAD_ERR_NO_TMP_DIR => 'Falta la carpeta temporal en el servidor.',
            UPLOAD_ERR_CANT_WRITE => 'No se pudo escribir el archivo en el disco.',
            UPLOAD_ERR_EXTENSION  => 'Una extensión de PHP detuvo la subida.'
        );
        $msg = isset($uploadErrors[$file['error']]) ? $uploadErrors[$file['error']] : 'Error al subir el archivo.';
        throw new RuntimeException($msg);
    }

    if ($isVideo) {
        $maxVideoBytes = 40 * 1024 * 1024;
        if ($file['size'] > $maxVideoBytes) {
            throw new InvalidArgumentException('El video supera los 40 MB permitidos.');
        }

        $originalName = (string) $file['name'];
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $allowedVideoExtensions = array('mp4', 'webm', 'mov');
        if (!in_array($extension, $allowedVideoExtensions, true)) {
            throw new InvalidArgumentException('Formato de video no válido. Se aceptan MP4, WebM o MOV.');
        }

        $ffprobeBin = admin_detect_binary('ffprobe');
        $ffmpegBin = admin_detect_binary('ffmpeg');

        $duration = null;

        if ($ffprobeBin && function_exists('shell_exec')) {
            $probeCmd = 'env -u LD_LIBRARY_PATH -u LD_PRELOAD ' . escapeshellarg($ffprobeBin) . ' -v error -show_entries format=duration,size -show_entries stream=width,height,codec_name -of json ' . escapeshellarg($file['tmp_name']) . ' 2>&1';
            $probeOut = @shell_exec($probeCmd);
            $probeData = json_decode((string) $probeOut, true);
            if ($probeData && isset($probeData['format']['duration'])) {
                $duration = (float) $probeData['format']['duration'];
            } else {
                admin_audit('upload_reel_probe_failed: ' . substr((string)$probeOut, 0, 200));
            }
        }

        // Fallback: Si no hay ffprobe o falló la sonda por restricciones del sistema, leer duración MP4 directamente por cabeceras
        if ($duration === null) {
            $parsedDuration = admin_read_mp4_duration($file['tmp_name']);
            if ($parsedDuration !== null && $parsedDuration > 0) {
                $duration = $parsedDuration;
            }
        }

        if ($duration === null) {
            if (!$ffprobeBin) {
                throw new InvalidArgumentException('El servidor no cuenta con ffprobe/ffmpeg instalado y no fue posible analizar el video.');
            }
            throw new InvalidArgumentException('El archivo subido no es un video legible o válido.');
        }

        if ($duration > 62.0) {
            throw new InvalidArgumentException('El video dura ' . round($duration) . ' segundos. Los reels deben durar un máximo de 60 segundos.');
        }

        $targetDir = dirname(__DIR__) . '/videos/instalaciones';
        if (!is_dir($targetDir) && !mkdir($targetDir, 0777, true)) {
            throw new RuntimeException('No se pudo acceder a la carpeta de videos.');
        }

        $rawBaseName = pathinfo($originalName, PATHINFO_FILENAME);
        $cleanSlug = preg_replace('/[^a-zA-Z0-9_\-]/', '-', strtolower($rawBaseName));
        $cleanSlug = preg_replace('/-+/', '-', trim($cleanSlug, '-'));
        if ($cleanSlug === '') {
            $cleanSlug = 'reel';
        }
        $cleanSlug = substr($cleanSlug, 0, 30);
        $token = bin2hex(random_bytes(4));
        $videoFilename = $cleanSlug . '-' . $token . '.mp4';
        $posterFilename = $cleanSlug . '-' . $token . '-poster.jpg';
        $targetPath = $targetDir . '/' . $videoFilename;
        $posterPath = $targetDir . '/' . $posterFilename;

        $processedByFfmpeg = false;
        if ($ffmpegBin && function_exists('exec')) {
            $ffmpegCmd = 'env -u LD_LIBRARY_PATH -u LD_PRELOAD ' . escapeshellarg($ffmpegBin) . ' -y -i ' . escapeshellarg($file['tmp_name']) . ' '
                . '-c:v libx264 -pix_fmt yuv420p -crf 26 -preset fast '
                . '-vf "scale=\'min(720,iw)\':-2" -r 30 '
                . '-c:a aac -b:a 128k '
                . '-movflags +faststart '
                . escapeshellarg($targetPath) . ' 2>&1';
            exec($ffmpegCmd, $ffOut, $ffRet);
            if ($ffRet === 0 && is_file($targetPath) && filesize($targetPath) > 0) {
                $processedByFfmpeg = true;
            }
        }

        if (!$processedByFfmpeg) {
            if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
                throw new RuntimeException('No fue posible procesar el video en el servidor.');
            }
        }
        chmod($targetPath, 0666);

        if ($ffmpegBin && function_exists('exec')) {
            $posterCmd = 'env -u LD_LIBRARY_PATH -u LD_PRELOAD ' . escapeshellarg($ffmpegBin) . ' -y -ss 00:00:00.500 -i ' . escapeshellarg($targetPath) . ' -frames:v 1 -update 1 -q:v 2 ' . escapeshellarg($posterPath) . ' 2>&1';
            exec($posterCmd);
            if (is_file($posterPath)) {
                chmod($posterPath, 0666);
            }
        }

        $relativeVideoPath = 'videos/instalaciones/' . $videoFilename;
        $relativePosterPath = is_file($posterPath) ? 'videos/instalaciones/' . $posterFilename : '';

        admin_audit('upload_reel: ' . $relativeVideoPath);

        echo json_encode(array(
            'success'  => true,
            'path'     => $relativeVideoPath,
            'poster'   => $relativePosterPath,
            'duration' => round($duration, 1),
            'size'     => filesize($targetPath)
        ));
        exit;
    }

    if ($isAudio) {
        $maxAudioBytes = 25 * 1024 * 1024;
        if ($file['size'] > $maxAudioBytes) {
            throw new InvalidArgumentException('El archivo de audio supera los 25 MB permitidos.');
        }

        $originalName = (string) $file['name'];
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $allowedAudioExtensions = array('mp3', 'ogg', 'wav', 'm4a');
        if (!in_array($extension, $allowedAudioExtensions, true)) {
            throw new InvalidArgumentException('Formato de audio no válido. Se aceptan archivos MP3, OGG, WAV o M4A.');
        }

        $targetDir = dirname(__DIR__) . '/audio';
        if (!is_dir($targetDir) && !mkdir($targetDir, 0777, true)) {
            throw new RuntimeException('No se pudo acceder a la carpeta de audio.');
        }

        $rawBaseName = pathinfo($originalName, PATHINFO_FILENAME);
        $cleanSlug = preg_replace('/[^a-zA-Z0-9_\-]/', '-', strtolower($rawBaseName));
        $cleanSlug = preg_replace('/-+/', '-', trim($cleanSlug, '-'));
        if ($cleanSlug === '') {
            $cleanSlug = 'audio';
        }
        $cleanSlug = substr($cleanSlug, 0, 35);
        $token = bin2hex(random_bytes(4));
        $audioFilename = $cleanSlug . '-' . $token . '.' . $extension;
        $targetPath = $targetDir . '/' . $audioFilename;

        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            throw new RuntimeException('No fue posible guardar el archivo de audio en el servidor.');
        }

        chmod($targetPath, 0666);
        $relativeAudioPath = 'audio/' . $audioFilename;

        admin_audit('upload_audio: ' . $relativeAudioPath);

        echo json_encode(array(
            'success' => true,
            'path'    => $relativeAudioPath,
            'size'    => filesize($targetPath)
        ));
        exit;
    }

    // Tamaño máximo: 2 MB
    $maxBytes = 2 * 1024 * 1024;
    if ($file['size'] > $maxBytes) {
        throw new InvalidArgumentException('La imagen no puede pesar más de 2 MB.');
    }

    // Validar extensión
    $originalName = (string) $file['name'];
    $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    $allowedExtensions = array('jpg', 'jpeg', 'png', 'webp');
    if (!in_array($extension, $allowedExtensions, true)) {
        throw new InvalidArgumentException('Formato no permitido. Solo se aceptan imágenes JPG, PNG o WebP.');
    }

    // Validar tipo MIME real y dimensiones con getimagesize
    $imageInfo = @getimagesize($file['tmp_name']);
    if ($imageInfo === false) {
        throw new InvalidArgumentException('El archivo subido no es una imagen válida o está dañado.');
    }

    $allowedMimes = array(
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp'
    );
    $mime = isset($imageInfo['mime']) ? $imageInfo['mime'] : '';
    if (!isset($allowedMimes[$mime])) {
        throw new InvalidArgumentException('Tipo de contenido no admitido.');
    }

    // Carpeta destino (directivos, novedades, instalaciones o servicios)
    $folder = isset($_POST['folder']) ? trim((string) $_POST['folder']) : 'directivos';
    $allowedFolders = array(
        'directivos'    => dirname(__DIR__) . '/images/directivos',
        'novedades'     => dirname(__DIR__) . '/images/novedades',
        'instalaciones' => dirname(__DIR__) . '/images/instalaciones',
        'servicios'     => dirname(__DIR__) . '/images/servicios'
    );

    if (!isset($allowedFolders[$folder])) {
        throw new InvalidArgumentException('Carpeta de destino no válida.');
    }

    $targetDir = $allowedFolders[$folder];
    if (!is_dir($targetDir) && !mkdir($targetDir, 0777, true)) {
        throw new RuntimeException('No se pudo acceder al directorio de almacenamiento de imágenes.');
    }

    // Nombre de archivo seguro
    $rawBaseName = pathinfo($originalName, PATHINFO_FILENAME);
    $cleanSlug = preg_replace('/[^a-zA-Z0-9_\-]/', '-', strtolower($rawBaseName));
    $cleanSlug = preg_replace('/-+/', '-', trim($cleanSlug, '-'));
    if ($cleanSlug === '') {
        $cleanSlug = 'imagen';
    }
    // Truncar a 30 caracteres para nombre base
    $cleanSlug = substr($cleanSlug, 0, 30);
    $normalizedExt = $allowedMimes[$mime];
    $finalFilename = $cleanSlug . '-' . bin2hex(random_bytes(4)) . '.' . $normalizedExt;
    $targetPath = $targetDir . '/' . $finalFilename;

    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        throw new RuntimeException('No fue posible guardar la imagen en el servidor.');
    }

    chmod($targetPath, 0666);
    $relativePublicPath = 'images/' . $folder . '/' . $finalFilename;

    admin_audit('upload_image: ' . $relativePublicPath);

    echo json_encode(array(
        'success' => true,
        'path'    => $relativePublicPath,
        'width'   => $imageInfo[0],
        'height'  => $imageInfo[1],
        'size'    => $file['size'],
        'mime'    => $mime
    ));
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(array(
        'success' => false,
        'error'   => $e->getMessage()
    ));
}
