<?php

/**
 * API: Inicio de Sesión de Afiliado
 * POST /api/auth/login.php
 */

require_once dirname(__DIR__) . '/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    api_error('Método no permitido. Utilice POST.', 405);
}

$input = api_input();

$usuario = isset($input['usuario']) ? trim($input['usuario']) : '';
$password = isset($input['password']) ? (string)$input['password'] : '';

if (empty($usuario) || empty($password)) {
    api_error('Debe ingresar su usuario (DNI o email) y contraseña.', 400);
}

try {
    $pdo = db_connect();

    // Permitir ingresar con DNI (sin puntos) o con Email
    $cleanDni = preg_replace('/[^0-9]/', '', $usuario);
    $cleanEmail = strtolower($usuario);

    if (!empty($cleanDni)) {
        $stmt = $pdo->prepare("SELECT * FROM `afiliados` 
                               WHERE `dni` = :dni OR `email` = :email 
                               LIMIT 1");
        $stmt->execute(array(
            ':dni'   => $cleanDni,
            ':email' => $cleanEmail
        ));
    } else {
        $stmt = $pdo->prepare("SELECT * FROM `afiliados` 
                               WHERE `email` = :email 
                               LIMIT 1");
        $stmt->execute(array(
            ':email' => $cleanEmail
        ));
    }
    $afiliado = $stmt->fetch();

    if (!$afiliado || empty($afiliado['password_hash'])) {
        api_error('Credenciales inválidas. Si aún no creaste tu contraseña, ingresá en "Registrarme".', 401);
    }

    if (!password_verify($password, $afiliado['password_hash'])) {
        api_error('Credenciales inválidas.', 401);
    }

    if ($afiliado['estado'] !== 'activo') {
        api_error('La cuenta se encuentra en estado: ' . htmlspecialchars($afiliado['estado']) . '. Contactate con el sindicato.', 403);
    }

    // Renovar token de sesión
    $authToken = bin2hex(random_bytes(32));
    $tokenExpires = date('Y-m-d H:i:s', strtotime('+90 days'));

    $updateStmt = $pdo->prepare("UPDATE `afiliados` SET
                                    `auth_token`         = :token,
                                    `auth_token_expires` = :expires,
                                    `ultimo_acceso`      = NOW()
                                 WHERE `id` = :id");
    $updateStmt->execute(array(
        ':token'   => $authToken,
        ':expires' => $tokenExpires,
        ':id'      => $afiliado['id']
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
            'email'             => $afiliado['email'],
            'telefono'          => $afiliado['telefono'],
            'empresa_panaderia' => $afiliado['empresa_panaderia'],
            'categoria_laboral' => $afiliado['categoria_laboral'],
            'estado'            => $afiliado['estado'],
            'foto_url'          => $afiliado['foto_url']
        ),
        'mensaje'         => 'Sesión iniciada correctamente.'
    ), 200);

} catch (Exception $e) {
    api_error('Error al iniciar sesión: ' . $e->getMessage(), 500);
}
