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

    if (!isset($_FILES['image']) || !is_array($_FILES['image'])) {
        throw new InvalidArgumentException('No se recibió ningún archivo de imagen.');
    }

    $file = $_FILES['image'];
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
