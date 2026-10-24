<?php

/**
 * Sembrador de Beneficios por Defecto en la Base de Datos
 * Ejecutar con: /opt/lampp/bin/php tools/seed-beneficios.php
 */

if (PHP_SAPI !== 'cli') {
    exit(1);
}

error_reporting(E_ALL);
ini_set('display_errors', '1');

$baseDir = dirname(__DIR__);
require_once $baseDir . '/db.php';

echo "=== CARGANDO BENEFICIOS POR DEFECTO EN BASE DE DATOS ===\n";

try {
    $pdo = db_connect();
} catch (Exception $e) {
    echo "[ERROR] No se pudo conectar a la base de datos: " . $e->getMessage() . "\n";
    exit(1);
}

$beneficios = array(
    array(
        'rubro' => 'salud',
        'nombre_comercio' => 'Laboratorios de Análisis Clínicos',
        'descuento_detalle' => 'Cobertura Sindical en Análisis',
        'descripcion' => 'Análisis clínicos, bacteriológicos y de alta complejidad para el afiliado y su familia.',
        'direccion' => 'Ituzaingó 1807, Lanús Este',
        'localidad' => 'Lanús Este',
        'telefono' => '1142253230',
        'whatsapp' => '',
        'coordenadas' => '-34.7061,-58.3925',
        'imagen_url' => '',
        'orden' => 1
    ),
    array(
        'rubro' => 'farmacia',
        'nombre_comercio' => 'Farmacia Sindical y Farmacias Adheridas',
        'descuento_detalle' => '50% Reintegro en Medicamentos',
        'descripcion' => 'Reintegro del 50% en medicamentos recetados para el afiliado titular y su grupo familiar a cargo.',
        'direccion' => 'Sitio de Montevideo 1640, Lanús Oeste',
        'localidad' => 'Lanús Oeste',
        'telefono' => '1142411920',
        'whatsapp' => '5491142411920',
        'coordenadas' => '-34.7035,-58.4076',
        'imagen_url' => 'images/servicios/captura-desde-2026-09-04-00-33-ea49bb9b.png',
        'orden' => 2
    ),
    array(
        'rubro' => 'optica',
        'nombre_comercio' => 'Convenio Ópticas Lanús',
        'descuento_detalle' => '100% Cobertura en Cristales y Armazones',
        'descripcion' => 'Entrega anual de anteojos recetados para el titular y familiares directos con orden médica.',
        'direccion' => 'Av. Hipólito Yrigoyen 3960, Lanús',
        'localidad' => 'Lanús',
        'telefono' => '1142411920',
        'whatsapp' => '5491142411920',
        'coordenadas' => '-34.7035,-58.4076',
        'imagen_url' => '',
        'orden' => 3
    ),
    array(
        'rubro' => 'subsidios',
        'nombre_comercio' => 'Subsidio por Nacimiento',
        'descuento_detalle' => 'Ajuar y Bolso de Pañales',
        'descripcion' => 'Entrega de bolso con ajuar completo y pañales descartables para acompañar la llegada del nuevo integrante.',
        'direccion' => 'Llavallol 902, Lanús Oeste',
        'localidad' => 'Lanús Oeste',
        'telefono' => '1142411920',
        'whatsapp' => '5491142411920',
        'coordenadas' => '-34.7035,-58.4076',
        'imagen_url' => '',
        'orden' => 4
    ),
    array(
        'rubro' => 'subsidios',
        'nombre_comercio' => 'Subsidio por Casamiento',
        'descuento_detalle' => 'Remise y Torta de Celebración',
        'descripcion' => 'Servicio de remise al Registro Civil o iglesia y una torta alegórica para celebrar tu matrimonio.',
        'direccion' => 'Llavallol 902, Lanús Oeste',
        'localidad' => 'Lanús Oeste',
        'telefono' => '1142411920',
        'whatsapp' => '5491142411920',
        'coordenadas' => '-34.7035,-58.4076',
        'imagen_url' => '',
        'orden' => 5
    ),
    array(
        'rubro' => 'subsidios',
        'nombre_comercio' => 'Subsidio por Mudanzas',
        'descuento_detalle' => 'Hasta 50% de Reintegro',
        'descripcion' => 'Reintegro de hasta el 50% del valor del servicio de flete o transporte de mudanza presentando boleta y recibo.',
        'direccion' => 'Llavallol 902, Lanús Oeste',
        'localidad' => 'Lanús Oeste',
        'telefono' => '1142411920',
        'whatsapp' => '5491142411920',
        'coordenadas' => '-34.7035,-58.4076',
        'imagen_url' => '',
        'orden' => 6
    ),
    array(
        'rubro' => 'turismo',
        'nombre_comercio' => 'Hotelería y Predios Recreativos',
        'descuento_detalle' => 'Tarifas Exclusivas para Afiliados',
        'descripcion' => 'Acceso a colonias de vacaciones, predios con pileta y hoteles sindicales en la Costa y sierras durante todo el año.',
        'direccion' => 'Consultar en Sede Sindical',
        'localidad' => 'Lanús Oeste',
        'telefono' => '1142411920',
        'whatsapp' => '5491142411920',
        'coordenadas' => '-34.7035,-58.4076',
        'imagen_url' => '',
        'orden' => 7
    )
);

$sql = "INSERT INTO `beneficios` (
    `rubro`, `nombre_comercio`, `descuento_detalle`, `descripcion`, 
    `direccion`, `localidad`, `telefono`, `whatsapp`, `coordenadas`, `imagen_url`, `orden`
) VALUES (
    :rubro, :nombre, :descuento, :descripcion, 
    :direccion, :localidad, :telefono, :whatsapp, :coordenadas, :imagen_url, :orden
)";

$stmt = $pdo->prepare($sql);
$insertados = 0;

foreach ($beneficios as $b) {
    try {
        $stmt->execute(array(
            ':rubro'       => $b['rubro'],
            ':nombre'      => $b['nombre_comercio'],
            ':descuento'   => $b['descuento_detalle'],
            ':descripcion' => $b['descripcion'],
            ':direccion'   => $b['direccion'],
            ':localidad'   => $b['localidad'],
            ':telefono'    => $b['telefono'],
            ':whatsapp'    => $b['whatsapp'],
            ':coordenadas' => $b['coordenadas'],
            ':imagen_url'  => $b['imagen_url'],
            ':orden'       => $b['orden']
        ));
        $insertados++;
    } catch (PDOException $e) {
        echo "Error insertando: " . $b['nombre_comercio'] . " - " . $e->getMessage() . "\n";
    }
}

echo "Beneficios insertados: " . $insertados . " de " . count($beneficios) . "\n";
