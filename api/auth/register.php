<?php

/**
 * API: Registro y Activación de Cuenta de Afiliado
 * POST /api/auth/register.php
 */

require_once dirname(__DIR__) . '/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    api_error('Método no permitido. Utilice POST.', 405);
}

$input = api_input();

$rawDni = isset($input['dni']) ? trim($input['dni']) : '';
$dni = preg_replace('/[^0-9]/', '', $rawDni);
$numeroAfiliado = isset($input['numero_afiliado']) ? trim($input['numero_afiliado']) : '';
$email = isset($input['email']) ? strtolower(trim($input['email'])) : '';
$password = isset($input['password']) ? (string)$input['password'] : '';
$telefono = isset($input['telefono']) ? trim($input['telefono']) : null;
$fotoUrl = isset($input['foto_url']) ? trim($input['foto_url']) : null;

// 1. Validaciones de entrada
if (empty($dni) || empty($numeroAfiliado)) {
    api_error('DNI y número de afiliado son obligatorios.', 400);
}

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    api_error('Debe ingresar una dirección de correo electrónico válida.', 400);
}

if (strlen($password) < 8) {
    api_error('La contraseña debe contener al menos 8 caracteres.', 400);
}

try {
    $pdo = db_connect();

    // 2. Buscar al afiliado en el padrón
    $stmt = $pdo->prepare("SELECT * FROM `afiliados` 
                           WHERE `dni` = :dni AND `numero_afiliado` = :numero 
                           LIMIT 1");
    $stmt->execute(array(
        ':dni'    => $dni,
        ':numero' => $numeroAfiliado
    ));
    $afiliado = $stmt->fetch();

    if (!$afiliado) {
        api_error('El DNI o número de afiliado no figura en el padrón.', 404);
    }

    if (!empty($afiliado['password_hash'])) {
        api_error('Este afiliado ya posee una cuenta activa. Por favor iniciá sesión.', 409);
    }

    if ($afiliado['estado'] !== 'activo') {
        api_error('El afiliado no se encuentra activo en el padrón.', 403);
    }

    // 3. Verificar que el email no esté ocupado por otro afiliado
    $emailStmt = $pdo->prepare("SELECT `id` FROM `afiliados` WHERE `email` = :email AND `id` != :id LIMIT 1");
    $emailStmt->execute(array(
        ':email' => $email,
        ':id'    => $afiliado['id']
    ));
    if ($emailStmt->fetch()) {
        api_error('La dirección de correo electrónico ya se encuentra registrada por otro usuario.', 409);
    }

    // 4. Crear hash seguro y generar token de sesión
    $passwordHash = password_hash($password, PASSWORD_BCRYPT);
    $authToken = bin2hex(random_bytes(32));
    $tokenExpires = date('Y-m-d H:i:s', strtotime('+90 days'));

    $updateStmt = $pdo->prepare("UPDATE `afiliados` SET
                                    `email`              = :email,
                                    `telefono`           = :telefono,
                                    `password_hash`      = :hash,
                                    `foto_url`           = :foto,
                                    `auth_token`         = :token,
                                    `auth_token_expires` = :expires,
                                    `fecha_registro`     = NOW(),
                                    `ultimo_acceso`      = NOW()
                                 WHERE `id` = :id");

    $updateStmt->execute(array(
        ':email'    => $email,
        ':telefono' => $telefono,
        ':hash'     => $passwordHash,
        ':foto'     => $fotoUrl,
        ':token'    => $authToken,
        ':expires'  => $tokenExpires,
        ':id'       => $afiliado['id']
    ));

    api_response(array(
        'auth_token'      => $authToken,
        'token_type'      => 'Bearer',
        'expires_in_days' => 90,
        'afiliado'        => array(
            'id'                => (int)$afiliado['id'],
            'dni'               => $afiliado['dni'],
            'numero_afiliado'   => $afiliado['numero_afiliado'],
            'nombre'            => $afiliado['nombre'],
            'apellido'          => $afiliado['apellido'],
            'email'             => $email,
            'telefono'          => $telefono,
            'empresa_panaderia' => $afiliado['empresa_panaderia'],
            'categoria_laboral' => $afiliado['categoria_laboral'],
            'estado'            => $afiliado['estado'],
            'foto_url'          => $fotoUrl
        ),
        'mensaje'         => '¡Cuenta activada con éxito!'
    ), 201);

} catch (Exception $e) {
    api_error('Error al registrar afiliado: ' . $e->getMessage(), 500);
}
