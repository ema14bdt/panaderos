<?php

/**
 * CLI Tool: Inicializar o restablecer el contenido local desde los seeds base.
 * Uso: php tools/seed-content.php [--force]
 */

$force = in_array('--force', $argv, true);
$baseDir = dirname(__DIR__) . '/private-content';
$seedsDir = $baseDir . '/seeds';

if (!is_dir($seedsDir)) {
    echo "Error: No existe el directorio de seeds en $seedsDir\n";
    exit(1);
}

$files = array(
    'home.json',
    'servicios.json',
    'normativas.json',
    'filiales.json',
    'comision.json',
    'novedades.json',
    'instalaciones.json'
);

echo "=== INICIALIZANDO CONTENIDO DESDE SEEDS ===\n\n";

$copied = 0;
foreach ($files as $file) {
    $seedFile = $seedsDir . '/' . $file;
    $targetFile = $baseDir . '/' . $file;

    if (!is_file($seedFile)) {
        echo " [SKIP] No se encontró seed para $file\n";
        continue;
    }

    if (is_file($targetFile) && !$force) {
        echo " [EXISTE] $file ya existe en el entorno (usá --force para sobreescribir)\n";
        continue;
    }

    if (is_file($targetFile) && $force) {
        @unlink($targetFile);
    }

    if (copy($seedFile, $targetFile)) {
        chmod($targetFile, 0666);
        echo " [OK] $file inicializado correctamente\n";
        $copied++;
    } else {
        echo " [ERROR] Falló la copia de $file\n";
    }
}

echo "\nOperación finalizada. $copied archivos inicializados.\n";
