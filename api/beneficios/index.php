<?php

/**
 * API: Catálogo de Beneficios, Convenios y Servicios
 * GET /api/beneficios/index.php
 * Parámetros opcionales (query string):
 *   - rubro: filtrar por rubro ('farmacia', 'optica', 'salud', 'turismo', 'subsidios', etc.)
 *   - q: búsqueda por texto en nombre, descripción o dirección
 */

require_once dirname(__DIR__) . '/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    api_error('Método no permitido. Utilice GET.', 405);
}

$rubroFiltro = isset($_GET['rubro']) ? strtolower(trim($_GET['rubro'])) : '';
$busqueda = isset($_GET['q']) ? strtolower(trim($_GET['q'])) : '';

$rubrosDisponibles = array(
    array('id' => 'todos',      'label' => 'Todos',               'icono' => 'apps'),
    array('id' => 'farmacia',   'label' => 'Farmacias',           'icono' => 'medication'),
    array('id' => 'optica',     'label' => 'Ópticas',             'icono' => 'visibility'),
    array('id' => 'salud',      'label' => 'Salud y Análisis',    'icono' => 'local_hospital'),
    array('id' => 'subsidios',  'label' => 'Subsidios y Ayuda',   'icono' => 'card_giftcard'),
    array('id' => 'turismo',    'label' => 'Turismo y Hotelería', 'icono' => 'beach_access'),
    array('id' => 'comercio',   'label' => 'Comercios Adheridos', 'icono' => 'storefront')
);

$beneficios = array();

// Intentar consultar base de datos MySQL
$consultadoEnDb = false;
try {
    $pdo = db_connect();
    $sql = "SELECT `id`, `rubro`, `nombre_comercio` AS `nombre`, `descuento_detalle`, 
                   `descripcion`, `direccion`, `localidad`, `telefono`, `whatsapp`, 
                   `coordenadas`, `imagen_url`, `activo`
            FROM `beneficios`
            WHERE `activo` = 1";
    $params = array();

    if (!empty($rubroFiltro) && $rubroFiltro !== 'todos') {
        $sql .= " AND `rubro` = :rubro";
        $params[':rubro'] = $rubroFiltro;
    }

    if (!empty($busqueda)) {
        $sql .= " AND (`nombre_comercio` LIKE :q1 OR `descripcion` LIKE :q2 OR `direccion` LIKE :q3)";
        $params[':q1'] = '%' . $busqueda . '%';
        $params[':q2'] = '%' . $busqueda . '%';
        $params[':q3'] = '%' . $busqueda . '%';
    }

    $sql .= " ORDER BY `orden` ASC, `nombre_comercio` ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();

    if (!empty($rows)) {
        $beneficios = $rows;
        $consultadoEnDb = true;
    }
} catch (Exception $e) {
    // Si la base de datos no está disponible temporalmente, caerá al fallback JSON
}

// Fallback: si la base de datos aún no tiene registros cargados, usar la información de servicios.json
if (!$consultadoEnDb) {
    $baseDir = dirname(dirname(__DIR__));
    $serviciosFile = $baseDir . '/private-content/servicios.json';
    if (!file_exists($serviciosFile)) {
        $serviciosFile = $baseDir . '/private-content/seeds/servicios.json';
    }

    $itemsFallback = array(
        array(
            'id' => 1,
            'rubro' => 'salud',
            'nombre' => 'Laboratorios de Análisis Clínicos',
            'descuento_detalle' => 'Cobertura Sindical en Análisis',
            'descripcion' => 'Análisis clínicos, bacteriológicos y de alta complejidad para el afiliado y su familia.',
            'direccion' => 'Ituzaingó 1807, Lanús Este',
            'localidad' => 'Lanús Este',
            'telefono' => '1142253230',
            'whatsapp' => '',
            'coordenadas' => '-34.7061,-58.3925',
            'imagen_url' => ''
        ),
        array(
            'id' => 2,
            'rubro' => 'farmacia',
            'nombre' => 'Farmacia Sindical y Farmacias Adheridas',
            'descuento_detalle' => '50% Reintegro en Medicamentos',
            'descripcion' => 'Reintegro del 50% en medicamentos recetados para el afiliado titular y su grupo familiar a cargo.',
            'direccion' => 'Sitio de Montevideo 1640, Lanús Oeste',
            'localidad' => 'Lanús Oeste',
            'telefono' => '1142411920',
            'whatsapp' => '5491142411920',
            'coordenadas' => '-34.7035,-58.4076',
            'imagen_url' => 'images/servicios/captura-desde-2026-09-04-00-33-ea49bb9b.png'
        ),
        array(
            'id' => 3,
            'rubro' => 'optica',
            'nombre' => 'Convenio Ópticas Lanús',
            'descuento_detalle' => '100% Cobertura en Cristales y Armazones',
            'descripcion' => 'Entrega anual de anteojos recetados para el titular y familiares directos con orden médica.',
            'direccion' => 'Av. Hipólito Yrigoyen 3960, Lanús',
            'localidad' => 'Lanús',
            'telefono' => '1142411920',
            'whatsapp' => '5491142411920',
            'coordenadas' => '-34.7035,-58.4076',
            'imagen_url' => ''
        ),
        array(
            'id' => 4,
            'rubro' => 'subsidios',
            'nombre' => 'Subsidio por Nacimiento',
            'descuento_detalle' => 'Ajuar y Bolso de Pañales',
            'descripcion' => 'Entrega de bolso con ajuar completo y pañales descartables para acompañar la llegada del nuevo integrante.',
            'direccion' => 'Llavallol 902, Lanús Oeste',
            'localidad' => 'Lanús Oeste',
            'telefono' => '1142411920',
            'whatsapp' => '5491142411920',
            'coordenadas' => '-34.7035,-58.4076',
            'imagen_url' => ''
        ),
        array(
            'id' => 5,
            'rubro' => 'subsidios',
            'nombre' => 'Subsidio por Casamiento',
            'descuento_detalle' => 'Remise y Torta de Celebración',
            'descripcion' => 'Servicio de remise al Registro Civil o iglesia y una torta alegórica para celebrar tu matrimonio.',
            'direccion' => 'Llavallol 902, Lanús Oeste',
            'localidad' => 'Lanús Oeste',
            'telefono' => '1142411920',
            'whatsapp' => '5491142411920',
            'coordenadas' => '-34.7035,-58.4076',
            'imagen_url' => ''
        ),
        array(
            'id' => 6,
            'rubro' => 'subsidios',
            'nombre' => 'Subsidio por Mudanzas',
            'descuento_detalle' => 'Hasta 50% de Reintegro',
            'descripcion' => 'Reintegro de hasta el 50% del valor del servicio de flete o transporte de mudanza presentando boleta y recibo.',
            'direccion' => 'Llavallol 902, Lanús Oeste',
            'localidad' => 'Lanús Oeste',
            'telefono' => '1142411920',
            'whatsapp' => '5491142411920',
            'coordenadas' => '-34.7035,-58.4076',
            'imagen_url' => ''
        ),
        array(
            'id' => 7,
            'rubro' => 'turismo',
            'nombre' => 'Hotelería y Predios Recreativos',
            'descuento_detalle' => 'Tarifas Exclusivas para Afiliados',
            'descripcion' => 'Acceso a colonias de vacaciones, predios con pileta y hoteles sindicales en la Costa y sierras durante todo el año.',
            'direccion' => 'Consultar en Sede Sindical',
            'localidad' => 'Lanús Oeste',
            'telefono' => '1142411920',
            'whatsapp' => '5491142411920',
            'coordenadas' => '-34.7035,-58.4076',
            'imagen_url' => ''
        )
    );

    // Filtrar fallback si se solicitaron filtros
    $beneficios = array();
    foreach ($itemsFallback as $item) {
        if (!empty($rubroFiltro) && $rubroFiltro !== 'todos' && $item['rubro'] !== $rubroFiltro) {
            continue;
        }
        if (!empty($busqueda)) {
            $textoCompleto = strtolower($item['nombre'] . ' ' . $item['descripcion'] . ' ' . $item['direccion']);
            if (strpos($textoCompleto, $busqueda) === false) {
                continue;
            }
        }
        $beneficios[] = $item;
    }
}

api_response(array(
    'rubros'     => $rubrosDisponibles,
    'total'      => count($beneficios),
    'beneficios' => array_values($beneficios)
), 200);
