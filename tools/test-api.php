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
    $parts = explode('?', $scriptPath, 2);
    $scriptFile = $parts[0];
    $queryString = isset($parts[1]) ? $parts[1] : '';
    $fullPath = $baseDir . '/' . ltrim($scriptFile, '/');

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
            $_SERVER["QUERY_STRING"] = ' . var_export($queryString, true) . ';
            parse_str($_SERVER["QUERY_STRING"], $_GET);
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

// 5. Prueba de Contenido de Inicio / Home
echo "\n5. Probando /api/home/index.php:\n";

$resHome = call_api('api/home/index.php', 'GET');
assert_api("Entrega contenido institucional de la home (lema, escala salarial, día del panadero y redes)", 
    isset($resHome['success']) && $resHome['success'] === true
    && !empty($resHome['data']['hero']['title'])
    && !empty($resHome['data']['escala_salarial']['pdf_url'])
    && !empty($resHome['data']['dia_panadero']['title'])
    && !empty($resHome['data']['social']['instagram_url'])
);

// 6. Prueba de Beneficios y Convenios
echo "\n6. Probando /api/beneficios/index.php:\n";

$resBeneficios = call_api('api/beneficios/index.php', 'GET');
assert_api("Entrega listado de beneficios y rubros", 
    isset($resBeneficios['success']) && $resBeneficios['success'] === true 
    && !empty($resBeneficios['data']['beneficios'])
    && !empty($resBeneficios['data']['rubros'])
);

$resFarmacias = call_api('api/beneficios/index.php?rubro=farmacia', 'GET');
assert_api("Filtra correctamente por rubro farmacia", 
    isset($resFarmacias['success']) && $resFarmacias['success'] === true 
    && !empty($resFarmacias['data']['beneficios'])
    && $resFarmacias['data']['beneficios'][0]['rubro'] === 'farmacia'
);

// 6. Prueba de Eventos
echo "\n6. Probando /api/eventos/index.php:\n";

$resEventosNoAuth = call_api('api/eventos/index.php', 'GET');
assert_api("Bloquea acceso a eventos sin token de sesión", isset($resEventosNoAuth['success']) && $resEventosNoAuth['success'] === false);

$resEventos = call_api('api/eventos/index.php', 'GET', array(), array(
    'Authorization' => 'Bearer ' . $token
));
assert_api("Entrega eventos activos y filiales con sesión", 
    isset($resEventos['success']) && $resEventos['success'] === true 
    && !empty($resEventos['data']['eventos'])
    && !empty($resEventos['data']['filiales'])
);

// 7. Prueba de Inscripción a Evento
echo "\n7. Probando /api/eventos/inscribirse.php:\n";

// Limpiar inscripciones previas de la prueba
$pdo->exec("DELETE FROM evento_inscripciones WHERE afiliado_id = 1");

$resInscripcionFail = call_api('api/eventos/inscribirse.php', 'POST', array(
    'evento_id' => 1,
    'filial_retiro_id' => 1,
    'beneficiarios' => array()
), array('Authorization' => 'Bearer ' . $token));
assert_api("Rechaza inscripción sin beneficiarios seleccionados", 
    isset($resInscripcionFail['success']) && $resInscripcionFail['success'] === false
);

$resInscripcion = call_api('api/eventos/inscribirse.php', 'POST', array(
    'evento_id' => 1,
    'filial_retiro_id' => 1,
    'observaciones' => 'Turno mañana',
    'beneficiarios' => array(
        array(
            'familiar_id' => 1,
            'talle' => '10',
            'nivel_escolar' => 'primaria'
        )
    )
), array('Authorization' => 'Bearer ' . $token));

assert_api("Registra inscripción y emite comprobante único", 
    isset($resInscripcion['success']) && $resInscripcion['success'] === true 
    && !empty($resInscripcion['data']['codigo_comprobante'])
    && strpos($resInscripcion['data']['codigo_comprobante'], 'UTILES-') === 0
);

// Re-consultar eventos para verificar que refleja la inscripción
$resEventosActualizados = call_api('api/eventos/index.php', 'GET', array(), array(
    'Authorization' => 'Bearer ' . $token
));
$eventoInscripto = null;
foreach ($resEventosActualizados['data']['eventos'] as $ev) {
    if ($ev['id'] == 1) {
        $eventoInscripto = $ev;
        break;
    }
}

assert_api("Listado de eventos refleja la inscripción y comprobante del afiliado", 
    $eventoInscripto !== null 
    && !empty($eventoInscripto['inscripcion_afiliado'])
    && $eventoInscripto['inscripcion_afiliado']['codigo_comprobante'] === $resInscripcion['data']['codigo_comprobante']
);

// Limpiar nuevamente la inscripción de prueba para dejar el estado limpio
$pdo->exec("DELETE FROM evento_inscripciones WHERE afiliado_id = 1");

// 8. Prueba de Cierre de Sesión y Eliminación de Cuenta (Apple App Store Guideline 5.1.1v)
echo "\n8. Probando /api/auth/logout.php y /api/auth/delete-account.php:\n";

$resLogout = call_api('api/auth/logout.php', 'POST', array(), array(
    'Authorization' => 'Bearer ' . $token
));
assert_api("Cierra sesión y revoca el token activo", 
    isset($resLogout['success']) && $resLogout['success'] === true
);

$resCarnetPostLogout = call_api('api/carnet/digital.php', 'GET', array(), array(
    'Authorization' => 'Bearer ' . $token
));
assert_api("Token revocado no permite acceder más al carnet", 
    isset($resCarnetPostLogout['success']) && $resCarnetPostLogout['success'] === false
);

// Volver a iniciar sesión para probar delete-account
$resReLogin = call_api('api/auth/login.php', 'POST', array(
    'usuario' => '28456123',
    'password' => 'Secreto12345'
));
$newToken = $resReLogin['data']['auth_token'];

$resDeleteBadPass = call_api('api/auth/delete-account.php', 'POST', array(
    'password' => 'incorrecta123'
), array('Authorization' => 'Bearer ' . $newToken));
assert_api("Rechaza eliminación de cuenta con contraseña errónea", 
    isset($resDeleteBadPass['success']) && $resDeleteBadPass['success'] === false
);

$resDelete = call_api('api/auth/delete-account.php', 'POST', array(
    'password' => 'Secreto12345'
), array('Authorization' => 'Bearer ' . $newToken));
assert_api("Elimina credenciales móviles del afiliado preservando su padrón", 
    isset($resDelete['success']) && $resDelete['success'] === true
);

// Comprobar en base de datos que se limpiaron las credenciales y el padrón persiste
$afiliadoDb = $pdo->query("SELECT * FROM afiliados WHERE dni = '28456123' LIMIT 1")->fetch();
assert_api("El padrón físico permanece intacto mientras que password y email se eliminaron", 
    $afiliadoDb !== false
    && $afiliadoDb['nombre'] === 'Juan Carlos'
    && empty($afiliadoDb['password_hash'])
    && empty($afiliadoDb['email'])
    && empty($afiliadoDb['auth_token'])
);

// 9-10. Pruebas de Endpoints de Administración Sindical
// Requieren la contraseña del administrador de pruebas por variable de entorno
// (no se almacena en el repositorio): PANADEROS_ADMIN_TEST_PASSWORD
$adminTestPassword = getenv('PANADEROS_ADMIN_TEST_PASSWORD');
if (empty($adminTestPassword)) {
    echo "\n9-10. [OMITIDAS] Setee PANADEROS_ADMIN_TEST_PASSWORD para probar los endpoints de administración.\n";
} else {

    // Iniciar sesión como administrador
    $resAdminLogin = call_api('api/auth/login.php', 'POST', array(
        'usuario' => '00000000',
        'password' => $adminTestPassword
    ));
    assert_api("Inicia sesión como administrador sindical", 
        isset($resAdminLogin['success']) && $resAdminLogin['success'] === true
        && isset($resAdminLogin['data']['afiliado']['rol'])
        && $resAdminLogin['data']['afiliado']['rol'] === 'admin'
    );
    $adminToken = $resAdminLogin['data']['auth_token'];

    // Consultar métricas
    $resMetricas = call_api('api/admin/metricas.php', 'GET', array(), array(
        'Authorization' => 'Bearer ' . $adminToken
    ));
    assert_api("Entrega métricas sindicales y de eventos a administradores", 
        isset($resMetricas['success']) && $resMetricas['success'] === true
        && isset($resMetricas['data']['total_padron'])
        && isset($resMetricas['data']['eventos_activos'])
    );

    // Validar comprobante inexistente
    $resCompInvalido = call_api('api/admin/validar-comprobante.php', 'POST', array(
        'codigo_comprobante' => 'UTILES-INEXISTENTE'
    ), array('Authorization' => 'Bearer ' . $adminToken));
    assert_api("Rechaza validación de comprobante no registrado", 
        isset($resCompInvalido['success']) && $resCompInvalido['success'] === false
    );

    // 10. Pruebas de Detalle de Afiliados, Inscriptos y Edición de Eventos
    echo "\n10. Probando detalle de afiliados, inscriptos y gestión de eventos:\n";

    // Listado de afiliados
    $resAfiliados = call_api('api/admin/afiliados.php', 'GET', array(), array(
        'Authorization' => 'Bearer ' . $adminToken
    ));
    assert_api("Entrega listado de afiliados con estado de padrón y app", 
        isset($resAfiliados['success']) && $resAfiliados['success'] === true
        && isset($resAfiliados['data']['afiliados'])
        && is_array($resAfiliados['data']['afiliados'])
        && count($resAfiliados['data']['afiliados']) > 0
    );

    // Listado de inscripciones
    $resInscripciones = call_api('api/admin/inscripciones.php', 'GET', array(), array(
        'Authorization' => 'Bearer ' . $adminToken
    ));
    assert_api("Entrega listado de inscriptos con datos de beneficiarios", 
        isset($resInscripciones['success']) && $resInscripciones['success'] === true
        && isset($resInscripciones['data']['inscripciones'])
        && is_array($resInscripciones['data']['inscripciones'])
    );

    // Listado de eventos para administración
    $resAdminEventos = call_api('api/admin/eventos.php', 'GET', array(), array(
        'Authorization' => 'Bearer ' . $adminToken
    ));
    assert_api("Entrega listado completo de eventos con estadísticas", 
        isset($resAdminEventos['success']) && $resAdminEventos['success'] === true
        && isset($resAdminEventos['data']['eventos'])
        && count($resAdminEventos['data']['eventos']) > 0
    );

    // Edición de un evento existente
    $resGuardarEvento = call_api('api/admin/guardar-evento.php', 'POST', array(
        'id' => 1,
        'titulo' => 'Campaña Útiles Escolares 2027 (Actualizado)',
        'descripcion' => 'Entrega de mochilas y kits escolares para hijos de afiliados.',
        'tipo' => 'utiles',
        'fecha_inicio_inscripcion' => '2027-02-01',
        'fecha_fin_inscripcion' => '2027-03-05',
        'fecha_entrega' => '2027-03-10',
        'edad_minima_hijo' => 3,
        'edad_maxima_hijo' => 17,
        'requiere_escolaridad' => 1,
        'requiere_talle' => 1,
        'estado' => 'abierto'
    ), array('Authorization' => 'Bearer ' . $adminToken));
    assert_api("Permite al administrador editar un evento correctamente", 
        isset($resGuardarEvento['success']) && $resGuardarEvento['success'] === true
        && isset($resGuardarEvento['data']['evento']['titulo'])
        && $resGuardarEvento['data']['evento']['titulo'] === 'Campaña Útiles Escolares 2027 (Actualizado)'
    );

}

echo "\n=======================================================\n";
echo "RESULTADO: " . $testsPassed . " de " . $totalTests . " pruebas superadas.\n";
echo "=======================================================\n";
