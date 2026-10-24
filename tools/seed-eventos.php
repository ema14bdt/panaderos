<?php

/**
 * Sembrador de Eventos y Filiales de Retiro
 * Ejecutar con: /opt/lampp/bin/php tools/seed-eventos.php
 */

if (PHP_SAPI !== 'cli') {
    exit(1);
}

error_reporting(E_ALL);
ini_set('display_errors', '1');

$baseDir = dirname(__DIR__);
require_once $baseDir . '/db.php';

echo "=== CARGANDO EVENTOS Y FILIALES POR DEFECTO EN BASE DE DATOS ===\n";

try {
    $pdo = db_connect();
} catch (Exception $e) {
    echo "[ERROR] No se pudo conectar a la base de datos: " . $e->getMessage() . "\n";
    exit(1);
}

// 1. Filiales de retiro
$filiales = array(
    array(
        'nombre' => 'Sede Central Lanús',
        'direccion' => 'Llavallol 902 esq. Rio de Janeiro',
        'localidad' => 'Lanús Oeste',
        'telefono' => '4241-1920',
        'horario_atencion' => 'Lunes a Viernes de 8:00 a 17:00 hs'
    ),
    array(
        'nombre' => 'Filial Avellaneda',
        'direccion' => 'Belgrano 1218',
        'localidad' => 'Avellaneda',
        'telefono' => '4203-6770',
        'horario_atencion' => 'Lunes a Viernes de 9:00 a 16:00 hs'
    ),
    array(
        'nombre' => 'Filial Adrogué · Alte. Brown',
        'direccion' => 'Muratore 995',
        'localidad' => 'Adrogué',
        'telefono' => '4214-1980',
        'horario_atencion' => 'Lunes a Viernes de 9:00 a 16:00 hs'
    ),
    array(
        'nombre' => 'Filial Florencio Varela',
        'direccion' => 'Alte. Brown 473',
        'localidad' => 'Florencio Varela',
        'telefono' => '4255-1747',
        'horario_atencion' => 'Lunes a Viernes de 9:00 a 16:00 hs'
    )
);

$stmtFilial = $pdo->prepare("INSERT INTO `filiales_retiro` (`nombre`, `direccion`, `localidad`, `telefono`, `horario_atencion`, `activo`, `created_at`) 
                             VALUES (:nombre, :direccion, :localidad, :telefono, :horario, 1, NOW())");

$filialesCount = 0;
foreach ($filiales as $f) {
    try {
        $stmtFilial->execute(array(
            ':nombre'    => $f['nombre'],
            ':direccion' => $f['direccion'],
            ':localidad' => $f['localidad'],
            ':telefono'  => $f['telefono'],
            ':horario'   => $f['horario_atencion']
        ));
        $filialesCount++;
    } catch (PDOException $e) {
        // Ignorar si ya existe
    }
}
echo "Filiales procesadas: " . $filialesCount . "\n";

// 2. Eventos iniciales
$eventos = array(
    array(
        'titulo' => 'Entrega de Kit de Útiles Escolares 2027',
        'descripcion' => 'Acompañamos el inicio de clases de las hijas e hijos de afiliados con la entrega gratuita de mochilas, guardapolvos y kits completos de útiles escolares para nivel inicial, primario y secundario.',
        'tipo' => 'utiles',
        'imagen_url' => '',
        'fecha_inicio_inscripcion' => date('Y-m-d', strtotime('-10 days')),
        'fecha_fin_inscripcion' => date('Y-m-d', strtotime('+90 days')),
        'fecha_entrega' => date('Y-m-d', strtotime('+45 days')),
        'edad_minima_hijo' => 3,
        'edad_maxima_hijo' => 17,
        'requiere_escolaridad' => 1,
        'requiere_talle' => 1,
        'estado' => 'abierto'
    ),
    array(
        'titulo' => 'Festejo y Entrega de Juguetes · Día del Niño',
        'descripcion' => 'Festejamos el Día de las Infancias con juegos, sorpresas y la entrega de juguetes para todos los niños y niñas del padrón de afiliados.',
        'tipo' => 'juguetes',
        'imagen_url' => '',
        'fecha_inicio_inscripcion' => date('Y-m-d', strtotime('-5 days')),
        'fecha_fin_inscripcion' => date('Y-m-d', strtotime('+60 days')),
        'fecha_entrega' => date('Y-m-d', strtotime('+30 days')),
        'edad_minima_hijo' => 0,
        'edad_maxima_hijo' => 12,
        'requiere_escolaridad' => 0,
        'requiere_talle' => 0,
        'estado' => 'abierto'
    )
);

$stmtEvento = $pdo->prepare("INSERT INTO `eventos` (
    `titulo`, `descripcion`, `tipo`, `imagen_url`, `fecha_inicio_inscripcion`, `fecha_fin_inscripcion`, 
    `fecha_entrega`, `edad_minima_hijo`, `edad_maxima_hijo`, `requiere_escolaridad`, `requiere_talle`, `estado`, `created_at`
) VALUES (
    :titulo, :descripcion, :tipo, :imagen_url, :inicio, :fin, 
    :entrega, :edad_min, :edad_max, :req_esc, :req_tal, :estado, NOW()
)");

$eventosCount = 0;
foreach ($eventos as $ev) {
    try {
        $stmtEvento->execute(array(
            ':titulo'      => $ev['titulo'],
            ':descripcion' => $ev['descripcion'],
            ':tipo'        => $ev['tipo'],
            ':imagen_url'  => $ev['imagen_url'],
            ':inicio'      => $ev['fecha_inicio_inscripcion'],
            ':fin'         => $ev['fecha_fin_inscripcion'],
            ':entrega'     => $ev['fecha_entrega'],
            ':edad_min'    => $ev['edad_minima_hijo'],
            ':edad_max'    => $ev['edad_maxima_hijo'],
            ':req_esc'     => $ev['requiere_escolaridad'],
            ':req_tal'     => $ev['requiere_talle'],
            ':estado'      => $ev['estado']
        ));
        $eventosCount++;
    } catch (PDOException $e) {
        // Ignorar si ya existe
    }
}
echo "Eventos insertados: " . $eventosCount . "\n";

// 3. Familiares de ejemplo para afiliado de prueba (Juan Carlos Perez, id 1)
$afiliado1 = $pdo->query("SELECT id FROM `afiliados` WHERE `dni` = '28456123' LIMIT 1")->fetch();
if ($afiliado1) {
    $afId = (int)$afiliado1['id'];
    $famCount = (int)$pdo->query("SELECT COUNT(*) FROM `afiliado_familiares` WHERE `afiliado_id` = $afId")->fetchColumn();
    if ($famCount === 0) {
        $stmtFam = $pdo->prepare("INSERT INTO `afiliado_familiares` 
            (`afiliado_id`, `nombre`, `apellido`, `dni`, `fecha_nacimiento`, `parentesco`, `escolaridad`, `created_at`) 
            VALUES (:af_id, :nombre, :apellido, :dni, :fecha_nac, :parentesco, :escolaridad, NOW())");

        $stmtFam->execute(array(
            ':af_id'       => $afId,
            ':nombre'      => 'Mateo',
            ':apellido'    => 'Perez',
            ':dni'         => '54123456',
            ':fecha_nac'   => (date('Y') - 8) . '-05-12',
            ':parentesco'  => 'hijo',
            ':escolaridad' => 'primaria'
        ));

        $stmtFam->execute(array(
            ':af_id'       => $afId,
            ':nombre'      => 'Sofía',
            ':apellido'    => 'Perez',
            ':dni'         => '56789123',
            ':fecha_nac'   => (date('Y') - 5) . '-09-20',
            ':parentesco'  => 'hija',
            ':escolaridad' => 'jardin'
        ));
        echo "Familiares de prueba insertados para afiliado $afId.\n";
    }
}

