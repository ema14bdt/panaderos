<?php

require_once 'bootstrap.php';
admin_require_login();

header('Content-Type: application/json; charset=utf-8');

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

        $probeCmd = 'env -u LD_LIBRARY_PATH -u LD_PRELOAD /usr/bin/ffprobe -v error -show_entries format=duration,size -show_entries stream=width,height,codec_name -of json ' . escapeshellarg($file['tmp_name']) . ' 2>&1';
        $probeOut = shell_exec($probeCmd);
        $probeData = json_decode((string) $probeOut, true);

        if (!$probeData || !isset($probeData['format']['duration'])) {
            admin_audit('upload_reel_probe_failed: ' . substr((string)$probeOut, 0, 200));
            throw new InvalidArgumentException('El archivo subido no es un video legible o válido.');
        }

        $duration = (float) $probeData['format']['duration'];
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

        $ffmpegCmd = 'env -u LD_LIBRARY_PATH -u LD_PRELOAD /usr/bin/ffmpeg -y -i ' . escapeshellarg($file['tmp_name']) . ' '
            . '-c:v libx264 -pix_fmt yuv420p -crf 26 -preset fast '
            . '-vf "scale=\'min(720,iw)\':-2" -r 30 '
            . '-c:a aac -b:a 128k '
            . '-movflags +faststart '
            . escapeshellarg($targetPath) . ' 2>&1';
        exec($ffmpegCmd, $ffOut, $ffRet);

        if ($ffRet !== 0 || !is_file($targetPath) || filesize($targetPath) === 0) {
            if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
                throw new RuntimeException('No fue posible procesar el video en el servidor.');
            }
        }
        chmod($targetPath, 0666);

        $posterCmd = 'env -u LD_LIBRARY_PATH -u LD_PRELOAD /usr/bin/ffmpeg -y -ss 00:00:00.500 -i ' . escapeshellarg($targetPath) . ' -frames:v 1 -update 1 -q:v 2 ' . escapeshellarg($posterPath) . ' 2>&1';
        exec($posterCmd);
        if (is_file($posterPath)) {
            chmod($posterPath, 0666);
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
