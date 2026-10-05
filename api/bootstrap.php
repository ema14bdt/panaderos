<?php

/**
 * Bootstrap API REST
 * Sindicato de Obreros Panaderos de Lanús
 */

// Headers de CORS y Tipo de Contenido
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Responder inmediatamente a peticiones preflight OPTIONS
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once dirname(__DIR__) . '/db.php';

/**
 * Obtener payload JSON recibido en el cuerpo de la petición
 */
function api_input() {
    $raw = file_get_contents('php://input');
    if (!$raw) {
        return array();
    }
    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : array();
}

/**
 * Responder en formato JSON estandarizado
 */
function api_response($data = null, $statusCode = 200, $error = null) {
    http_response_code($statusCode);
    $payload = array(
        'success'   => ($statusCode >= 200 && $statusCode < 300),
        'data'      => $data,
        'error'     => $error,
        'timestamp' => date('c')
    );
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/**
 * Helper para responder error
 */
function api_error($message, $statusCode = 400, $data = null) {
    api_response($data, $statusCode, $message);
}

/**
 * Obtener token Bearer del header Authorization
 */
function api_get_bearer_token() {
    $headers = null;
    if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
        $headers = trim($_SERVER['HTTP_AUTHORIZATION']);
    } elseif (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
        $headers = trim($_SERVER['REDIRECT_HTTP_AUTHORIZATION']);
    } elseif (function_exists('apache_request_headers')) {
        $requestHeaders = apache_request_headers();
        $requestHeaders = array_combine(
            array_map('ucwords', array_keys($requestHeaders)),
            array_values($requestHeaders)
        );
        if (isset($requestHeaders['Authorization'])) {
            $headers = trim($requestHeaders['Authorization']);
        }
    }

    if (!empty($headers) && preg_match('/Bearer\s(\S+)/i', $headers, $matches)) {
        return $matches[1];
    }
    return null;
}

/**
 * Validar autenticación de afiliado mediante Bearer Token
 * @return array Datos del afiliado autenticado
 */
function api_require_auth() {
    $token = api_get_bearer_token();
    if (!$token) {
        api_error('Encabezado de autorización ausente o inválido.', 401);
    }

    try {
        $pdo = db_connect();
        $stmt = $pdo->prepare("SELECT * FROM `afiliados` 
                               WHERE `auth_token` = :token 
                                 AND (`auth_token_expires` IS NULL OR `auth_token_expires` > NOW())
                               LIMIT 1");
        $stmt->execute(array(':token' => $token));
        $afiliado = $stmt->fetch();

        if (!$afiliado) {
            api_error('Sesión inválida o expirada. Por favor vuelva a iniciar sesión.', 401);
        }

        if ($afiliado['estado'] !== 'activo') {
            api_error('La cuenta del afiliado no se encuentra activa en el padrón.', 403);
        }

        return $afiliado;
    } catch (Exception $e) {
        api_error('Error al verificar sesión: ' . $e->getMessage(), 500);
    }
}
