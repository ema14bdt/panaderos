<?php

/**
 * API Administrador: Métricas Generales del Sindicato y Campañas
 * GET /api/admin/metricas.php
 * Header: Authorization: Bearer <auth_token> (Requiere rol admin)
 */

require_once dirname(__DIR__) . '/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    api_error('Método no permitido. Utilice GET.', 405);
}

// 1. Validar autenticación de administrador
$afiliado = api_require_auth();
if (!isset($afiliado['rol']) || $afiliado['rol'] !== 'admin') {
    api_error('Acceso restringido a administradores sindicales.', 403);
}

try {
    $pdo = db_connect();

    // Total de empadronados
    $totalPadron = (int)$pdo->query("SELECT COUNT(*) FROM `afiliados`")->fetchColumn();

    // Usuarios activos registrados en la app
    $usuariosApp = (int)$pdo->query("SELECT COUNT(*) FROM `afiliados` WHERE `email` IS NOT NULL AND `password_hash` IS NOT NULL")->fetchColumn();

    // Campañas activas
    $eventosActivos = (int)$pdo->query("SELECT COUNT(*) FROM `eventos` WHERE `estado` = 'abierto'")->fetchColumn();

    // Total inscripciones
    $totalInscripciones = (int)$pdo->query("SELECT COUNT(*) FROM `evento_inscripciones`")->fetchColumn();

    // Pendientes vs Entregados
    $pendientesRetiro = (int)$pdo->query("SELECT COUNT(*) FROM `evento_inscripciones` WHERE `estado` = 'solicitado'")->fetchColumn();
    $entregados = (int)$pdo->query("SELECT COUNT(*) FROM `evento_inscripciones` WHERE `estado` = 'entregado'")->fetchColumn();

    // Total de hijos beneficiarios anotados
    $totalBeneficiarios = (int)$pdo->query("SELECT COUNT(*) FROM `evento_inscripcion_beneficiarios`")->fetchColumn();

    // Desglose de talles de guardapolvo solicitados
    $tallesStmt = $pdo->query("SELECT `talle`, COUNT(*) AS `cantidad` 
                               FROM `evento_inscripcion_beneficiarios` 
                               WHERE `talle` IS NOT NULL AND `talle` != '' 
                               GROUP BY `talle` 
                               ORDER BY `cantidad` DESC");
    $tallesResumen = $tallesStmt->fetchAll();

    api_response(array(
        'total_padron'        => $totalPadron,
        'usuarios_app'        => $usuariosApp,
        'tasa_adopcion'       => $totalPadron > 0 ? round(($usuariosApp / $totalPadron) * 100, 1) : 0,
        'eventos_activos'     => $eventosActivos,
        'total_inscripciones' => $totalInscripciones,
        'pendientes_retiro'   => $pendientesRetiro,
        'entregados'          => $entregados,
        'total_beneficiarios' => $totalBeneficiarios,
        'talles_resumen'      => $tallesResumen,
    ), 200);

} catch (Exception $e) {
    api_error('Error al obtener métricas de administración: ' . $e->getMessage(), 500);
}
