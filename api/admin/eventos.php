<?php

/**
 * API Administrador: Listado completo de Eventos con conteo de inscripciones
 * GET /api/admin/eventos.php
 * Header: Authorization: Bearer <auth_token> (Requiere rol admin)
 */

require_once dirname(__DIR__) . '/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    api_error('Método no permitido. Utilice GET.', 405);
}

// 1. Validar autenticación de administrador
$admin = api_require_auth();
if (!isset($admin['rol']) || $admin['rol'] !== 'admin') {
    api_error('Acceso restringido a administradores sindicales.', 403);
}

try {
    $pdo = db_connect();

    $sql = "SELECT e.*,
                   (SELECT COUNT(*) FROM `evento_inscripciones` WHERE `evento_id` = e.`id`) AS `total_inscriptos`,
                   (SELECT COUNT(*) FROM `evento_inscripciones` WHERE `evento_id` = e.`id` AND `estado` = 'solicitado') AS `total_pendientes`,
                   (SELECT COUNT(*) FROM `evento_inscripciones` WHERE `evento_id` = e.`id` AND `estado` = 'entregado') AS `total_entregados`,
                   (SELECT COUNT(*) FROM `evento_inscripcion_beneficiarios` b 
                    JOIN `evento_inscripciones` i ON b.`inscripcion_id` = i.`id` 
                    WHERE i.`evento_id` = e.`id`) AS `total_beneficiarios`
            FROM `eventos` e
            ORDER BY e.`id` DESC";

    $stmt = $pdo->query($sql);
    $rows = $stmt->fetchAll();

    $eventos = array();
    foreach ($rows as $row) {
        $eventos[] = array(
            'id'                       => (int)$row['id'],
            'titulo'                   => $row['titulo'],
            'descripcion'              => $row['descripcion'],
            'tipo'                     => $row['tipo'],
            'imagen_url'               => $row['imagen_url'],
            'fecha_inicio_inscripcion' => $row['fecha_inicio_inscripcion'],
            'fecha_fin_inscripcion'    => $row['fecha_fin_inscripcion'],
            'fecha_entrega'            => $row['fecha_entrega'],
            'edad_minima_hijo'         => (int)$row['edad_minima_hijo'],
            'edad_maxima_hijo'         => (int)$row['edad_maxima_hijo'],
            'requiere_escolaridad'     => (bool)$row['requiere_escolaridad'],
            'requiere_talle'           => (bool)$row['requiere_talle'],
            'estado'                   => $row['estado'],
            'total_inscriptos'         => (int)$row['total_inscriptos'],
            'total_pendientes'         => (int)$row['total_pendientes'],
            'total_entregados'         => (int)$row['total_entregados'],
            'total_beneficiarios'      => (int)$row['total_beneficiarios'],
            'created_at'               => $row['created_at'],
        );
    }

    api_response(array(
        'total'   => count($eventos),
        'eventos' => $eventos
    ));
} catch (Exception $e) {
    api_error('Error al consultar eventos: ' . $e->getMessage(), 500);
}
