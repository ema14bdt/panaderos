<?php

/**
 * CLI Tool: Exportar el contenido del entorno actual a la carpeta seeds base.
 * Uso: php tools/export-content.php
 */

$baseDir = dirname(__DIR__) . '/private-content';
$seedsDir = $baseDir . '/seeds';

if (!is_dir($seedsDir)) {
    mkdir($seedsDir, 0777, true);
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

echo "=== EXPORTANDO CONTENIDO ACTUAL A SEEDS BASE ===\n\n";

$exported = 0;
foreach ($files as $file) {
    $sourceFile = $baseDir . '/' . $file;
    $seedFile = $seedsDir . '/' . $file;

    if (!is_file($sourceFile)) {
        echo " [SKIP] No existe $file en el entorno actual\n";
        continue;
    }

    if (copy($sourceFile, $seedFile)) {
        chmod($seedFile, 0666);
        echo " [OK] $file exportado a seeds/\n";
        $exported++;
    } else {
        echo " [ERROR] Falló la exportación de $file\n";
    }
}

echo "\nOperación finalizada. $exported archivos actualizados en seeds/.\n";
