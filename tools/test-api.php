<?php

/**
 * Suite de Pruebas Automatizadas de la API REST
 * Ejecución vía CLI: /opt/lampp/bin/php tools/test-api.php
 */

if (PHP_SAPI !== 'cli') {
    exit(1);
}

error_reporting(E_ALL);
ini_set('display_errors', '1');

$baseDir = dirname(__DIR__);
require_once $baseDir . '/db.php';

echo "=== INICIANDO PRUEBAS DE ENDPOINTS DE LA API REST ===\n\n";

$testsPassed = 0;
$totalTests = 0;

function assert_api($description, $condition) {
    global $testsPassed, $totalTests;
    $totalTests++;
    if ($condition) {
        $testsPassed++;
        echo "  [OK] " . $description . "\n";
    } else {
        echo "  [FAIL] " . $description . "\n";
    }
}

/**
 * Helper para simular llamadas HTTP a los scripts PHP directamente
 */
function call_api($scriptPath, $method = 'GET', $body = array(), $headers = array()) {
    $baseDir = dirname(__DIR__);
    $fullPath = $baseDir . '/' . ltrim($scriptPath, '/');

    // Preparar variables de servidor simuladas
    $_SERVER['REQUEST_METHOD'] = strtoupper($method);
    $_SERVER['CONTENT_TYPE'] = 'application/json';

    foreach ($headers as $k => $v) {
        $serverKey = 'HTTP_' . strtoupper(str_replace('-', '_', $k));
        $_SERVER[$serverKey] = $v;
    }

    // Stream wrapper o stdin simulado
    $payloadJson = json_encode($body);

    // Ejecutar vía subproceso PHP para aislar cabeceras y exit()
    $cmd = sprintf(
        '%s -d display_errors=0 -r %s',
        escapeshellarg('/opt/lampp/bin/php'),
        escapeshellarg('
            $_SERVER["REQUEST_METHOD"] = "' . $method . '";
            $_SERVER["CONTENT_TYPE"] = "application/json";
            ' . (isset($headers['Authorization']) ? '$_SERVER["HTTP_AUTHORIZATION"] = "' . addslashes($headers['Authorization']) . '";' : '') . '
            
            // Simular php://input
            $input = ' . var_export($payloadJson, true) . ';
            // Mock stream
            stream_wrapper_unregister("php");
            class MockPhpStream {
                public $context;
                private $data;
                private $pos = 0;
                public function stream_open($path, $mode, $options, &$opened_path) {
                    global $input;
                    $this->data = $input;
                    $this->pos = 0;
                    return true;
                }
                public function stream_read($count) {
                    $ret = substr($this->data, $this->pos, $count);
                    $this->pos += strlen($ret);
                    return $ret;
                }
                public function stream_eof() {
                    return $this->pos >= strlen($this->data);
                }
                public function stream_stat() { return array(); }
            }
            stream_wrapper_register("php", "MockPhpStream");

            require ' . var_export($fullPath, true) . ';
        ')
    );

    $output = shell_exec($cmd);
    $decoded = json_decode($output, true);
    return is_array($decoded) ? $decoded : array('raw' => $output);
}

// Limpiar afiliado 28456123 antes de empezar para pruebas consistentes
$pdo = db_connect();
$pdo->exec("UPDATE afiliados SET email = NULL, password_hash = NULL, auth_token = NULL, fecha_registro = NULL WHERE dni = '28456123'");

// 1. Prueba de Validación de Padrón
echo "1. Probando /api/auth/validar-padron.php:\n";

$resNotFound = call_api('api/auth/validar-padron.php', 'POST', array(
    'dni' => '99999999',
    'numero_afiliado' => '0000'
));
assert_api("Rechaza DNI inexistente con error adecuado", isset($resNotFound['success']) && $resNotFound['success'] === false);

$resMissing = call_api('api/auth/validar-padron.php', 'POST', array('dni' => ''));
assert_api("Rechaza petición con campos faltantes", isset($resMissing['success']) && $resMissing['success'] === false);

$resValid = call_api('api/auth/validar-padron.php', 'POST', array(
    'dni' => '28456123',
    'numero_afiliado' => '10452'
));
assert_api("Valida afiliado existente del padrón", isset($resValid['success']) && $resValid['success'] === true && $resValid['data']['nombre'] === 'Juan Carlos');

// 2. Prueba de Registro
echo "\n2. Probando /api/auth/register.php:\n";

$resPassShort = call_api('api/auth/register.php', 'POST', array(
    'dni' => '28456123',
    'numero_afiliado' => '10452',
    'email' => 'juan@panaderos.com',
    'password' => '123'
));
assert_api("Rechaza contraseña menor a 8 caracteres", isset($resPassShort['success']) && $resPassShort['success'] === false);

$resReg = call_api('api/auth/register.php', 'POST', array(
    'dni' => '28456123',
    'numero_afiliado' => '10452',
    'email' => 'juan.perez@panaderos.com',
    'password' => 'Secreto12345',
    'telefono' => '11-4214-5555'
));
assert_api("Registra y activa la cuenta del afiliado", isset($resReg['success']) && $resReg['success'] === true && !empty($resReg['data']['auth_token']));

$resRegAgain = call_api('api/auth/register.php', 'POST', array(
    'dni' => '28456123',
    'numero_afiliado' => '10452',
    'email' => 'juan.perez@panaderos.com',
    'password' => 'Secreto12345'
));
assert_api("Bloquea intento de re-registro sobre cuenta ya activa", isset($resRegAgain['success']) && $resRegAgain['success'] === false);

// 3. Prueba de Login
echo "\n3. Probando /api/auth/login.php:\n";

$resWrongPass = call_api('api/auth/login.php', 'POST', array(
    'usuario' => '28456123',
    'password' => 'ClaveIncorrecta'
));
assert_api("Rechaza contraseña errónea", isset($resWrongPass['success']) && $resWrongPass['success'] === false);

$resLoginDni = call_api('api/auth/login.php', 'POST', array(
    'usuario' => '28456123',
    'password' => 'Secreto12345'
));
assert_api("Inicia sesión exitosamente con DNI", isset($resLoginDni['success']) && $resLoginDni['success'] === true && !empty($resLoginDni['data']['auth_token']));

$resLoginEmail = call_api('api/auth/login.php', 'POST', array(
    'usuario' => 'juan.perez@panaderos.com',
    'password' => 'Secreto12345'
));
assert_api("Inicia sesión exitosamente con Email", isset($resLoginEmail['success']) && $resLoginEmail['success'] === true);

// El último login renovó el token activo
$token = isset($resLoginEmail['data']['auth_token']) ? $resLoginEmail['data']['auth_token'] : (isset($resLoginDni['data']['auth_token']) ? $resLoginDni['data']['auth_token'] : '');

// 4. Prueba de Carnet Digital
echo "\n4. Probando /api/carnet/digital.php:\n";

$resNoAuth = call_api('api/carnet/digital.php', 'GET');
assert_api("Bloquea acceso al carnet sin token", isset($resNoAuth['success']) && $resNoAuth['success'] === false);

$resCarnet = call_api('api/carnet/digital.php', 'GET', array(), array(
    'Authorization' => 'Bearer ' . $token
));
assert_api("Entrega datos completos del carnet digital con Bearer token", 
    isset($resCarnet['success']) && $resCarnet['success'] === true 
    && isset($resCarnet['data']['carnet']['qr_payload'])
    && $resCarnet['data']['carnet']['dni'] === '28456123'
);

echo "\n=======================================================\n";
echo "RESULTADO: " . $testsPassed . " de " . $totalTests . " pruebas superadas.\n";
echo "=======================================================\n";
