<?php

/**
 * API: Cierre de Sesión del Afiliado
 * POST /api/auth/logout.php
 * Header: Authorization: Bearer <auth_token>
 */

require_once dirname(__DIR__) . '/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    api_error('Método no permitido. Utilice POST.', 405);
}

// 1. Validar autenticación activa
$afiliado = api_require_auth();
$afiliadoId = (int)$afiliado['id'];

try {
    $pdo = db_connect();

    // 2. Revocar token de autenticación
    $stmt = $pdo->prepare("UPDATE `afiliados` SET 
        `auth_token` = NULL,
        `auth_token_expires` = NULL
        WHERE `id` = :id");

    $stmt->execute(array(':id' => $afiliadoId));

    api_response(array(
        'mensaje' => 'Sesión cerrada correctamente.'
    ), 200);

} catch (Exception $e) {
    api_error('Error al cerrar sesión: ' . $e->getMessage(), 500);
}
