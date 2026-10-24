<?php

/**
 * API: Listado de Eventos Activos, Estado de Inscripciones y Filiales de Retiro
 * GET /api/eventos/index.php
 * Header: Authorization: Bearer <auth_token>
 */

require_once dirname(__DIR__) . '/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    api_error('Método no permitido. Utilice GET.', 405);
}

// Requiere sesión de afiliado activa
$afiliado = api_require_auth();
$afiliadoId = (int)$afiliado['id'];

$eventos = array();
$filiales = array();
$familiares = array();

$consultadoEnDb = false;

try {
    $pdo = db_connect();

    // 1. Obtener eventos abiertos / activos
    $stmtEventos = $pdo->query("SELECT * FROM `eventos` 
                                WHERE `estado` IN ('abierto', 'cerrado') 
                                ORDER BY `fecha_inicio_inscripcion` DESC");
    $eventosDb = $stmtEventos->fetchAll();

    // 2. Obtener filial de retiro oficial para eventos (Sede Central Lanús)
    $stmtFiliales = $pdo->query("SELECT `id`, `nombre`, `direccion`, `localidad`, `telefono`, `horario_atencion` 
                                 FROM `filiales_retiro` 
                                 WHERE `activo` = 1 AND (`localidad` LIKE '%Lanús%' OR `id` = 1)
                                 ORDER BY `id` ASC
                                 LIMIT 1");
    $filialesDb = $stmtFiliales->fetchAll();

    // 3. Obtener grupo familiar del afiliado
    $stmtFam = $pdo->prepare("SELECT `id`, `nombre`, `apellido`, `dni`, `fecha_nacimiento`, `parentesco`, `escolaridad` 
                              FROM `afiliado_familiares` 
                              WHERE `afiliado_id` = :afiliado_id 
                              ORDER BY `fecha_nacimiento` DESC");
    $stmtFam->execute(array(':afiliado_id' => $afiliadoId));
    $familiares = $stmtFam->fetchAll();

    if (!empty($eventosDb)) {
        // 4. Para cada evento, verificar si el afiliado ya se inscribió
        $stmtInscripcion = $pdo->prepare("SELECT i.*, f.`nombre` AS `filial_nombre`, f.`direccion` AS `filial_direccion`
                                          FROM `evento_inscripciones` i
                                          LEFT JOIN `filiales_retiro` f ON i.`filial_retiro_id` = f.`id`
                                          WHERE i.`evento_id` = :evento_id AND i.`afiliado_id` = :afiliado_id
                                          LIMIT 1");

        $stmtBeneficiarios = $pdo->prepare("SELECT b.*, fam.`nombre`, fam.`apellido`, fam.`dni`, fam.`parentesco`
                                            FROM `evento_inscripcion_beneficiarios` b
                                            JOIN `afiliado_familiares` fam ON b.`familiar_id` = fam.`id`
                                            WHERE b.`inscripcion_id` = :inscripcion_id");

        foreach ($eventosDb as $ev) {
            $stmtInscripcion->execute(array(
                ':evento_id'   => $ev['id'],
                ':afiliado_id' => $afiliadoId
            ));
            $inscripcion = $stmtInscripcion->fetch();

            if ($inscripcion) {
                $stmtBeneficiarios->execute(array(':inscripcion_id' => $inscripcion['id']));
                $inscripcion['beneficiarios'] = $stmtBeneficiarios->fetchAll();
            }

            $ev['inscripcion_afiliado'] = $inscripcion ? $inscripcion : null;
            $eventos[] = $ev;
        }

        $filiales = $filialesDb;
        $consultadoEnDb = true;
    }
} catch (Exception $e) {
    // Si la base no está disponible temporalmente, continuará al fallback
}

// Fallback estructurado si aún no hay eventos cargados en MySQL
if (!$consultadoEnDb) {
    $eventos = array(
        array(
            'id' => 1,
            'titulo' => 'Entrega de Kit de Útiles Escolares 2027',
            'descripcion' => 'Acompañamos el inicio de clases de las hijas e hijos de afiliados con la entrega gratuita de mochilas, guardapolvos y kits completos de útiles escolares para nivel inicial, primario y secundario.',
            'tipo' => 'utiles',
            'imagen_url' => '',
            'fecha_inicio_inscripcion' => date('Y') . '-01-15',
            'fecha_fin_inscripcion' => (date('Y') + 1) . '-03-10',
            'fecha_entrega' => (date('Y') + 1) . '-02-25',
            'edad_minima_hijo' => 3,
            'edad_maxima_hijo' => 17,
            'requiere_escolaridad' => 1,
            'requiere_talle' => 1,
            'estado' => 'abierto',
            'inscripcion_afiliado' => null
        ),
        array(
            'id' => 2,
            'titulo' => 'Festejo y Entrega de Juguetes · Día del Niño',
            'descripcion' => 'Festejamos el Día de las Infancias con juegos, sorpresas y la entrega de juguetes para todos los niños y niñas del padrón de afiliados.',
            'tipo' => 'juguetes',
            'imagen_url' => '',
            'fecha_inicio_inscripcion' => date('Y') . '-07-01',
            'fecha_fin_inscripcion' => date('Y') . '-08-15',
            'fecha_entrega' => date('Y') . '-08-20',
            'edad_minima_hijo' => 0,
            'edad_maxima_hijo' => 12,
            'requiere_escolaridad' => 0,
            'requiere_talle' => 0,
            'estado' => 'abierto',
            'inscripcion_afiliado' => null
        )
    );

    $filiales = array(
        array(
            'id' => 1,
            'nombre' => 'Sede Central Lanús',
            'direccion' => 'Llavallol 902 esq. Rio de Janeiro',
            'localidad' => 'Lanús Oeste',
            'telefono' => '4241-1920',
            'horario_atencion' => 'Lunes a Viernes de 8:00 a 17:00 hs'
        )
    );
}

api_response(array(
    'eventos'    => $eventos,
    'filiales'   => $filiales,
    'familiares' => $familiares
), 200);
