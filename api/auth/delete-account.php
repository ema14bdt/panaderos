<?php

/**
 * API: Eliminación de Cuenta de Usuario Móvil (Apple App Store Guideline 5.1.1v)
 * POST /api/auth/delete-account.php
 * Header: Authorization: Bearer <auth_token>
 */

require_once dirname(__DIR__) . '/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    api_error('Método no permitido. Utilice POST.', 405);
}

// 1. Validar autenticación activa
$afiliado = api_require_auth();
$afiliadoId = (int)$afiliado['id'];

$input = api_input();
$password = isset($input['password']) ? (string)$input['password'] : '';

// Si se envía contraseña, verificar que coincida por seguridad adicional
if (!empty($password) && !empty($afiliado['password_hash'])) {
    if (!password_verify($password, $afiliado['password_hash'])) {
        api_error('La contraseña ingresada es incorrecta.', 403);
    }
}

try {
    $pdo = db_connect();

    // 2. Limpiar credenciales y accesos móviles del afiliado (preservando el legajo sindical del padrón)
    $stmt = $pdo->prepare("UPDATE `afiliados` SET 
        `email` = NULL,
        `telefono` = NULL,
        `password_hash` = NULL,
        `foto_url` = NULL,
        `auth_token` = NULL,
        `auth_token_expires` = NULL,
        `reset_token` = NULL,
        `reset_token_expires` = NULL,
        `fecha_registro` = NULL,
        `ultimo_acceso` = NULL
        WHERE `id` = :id");

    $stmt->execute(array(':id' => $afiliadoId));

    // 3. Eliminar tokens de notificaciones push registrados para este dispositivo/afiliado
    try {
        $stmtPush = $pdo->prepare("DELETE FROM `dispositivos_push` WHERE `afiliado_id` = :id");
        $stmtPush->execute(array(':id' => $afiliadoId));
    } catch (Exception $e) {
        // Ignorar si la tabla no tuviese registros
    }

    api_response(array(
        'mensaje' => 'Tu cuenta en la app móvil y tus credenciales de acceso han sido eliminadas correctamente.'
    ), 200);

} catch (Exception $e) {
    api_error('Error al procesar la eliminación de la cuenta: ' . $e->getMessage(), 500);
}
