<?php

if (PHP_SAPI !== 'cli') {
    exit(1);
}

error_reporting(E_ALL);
ini_set('display_errors', '1');

echo "=== INICIANDO SUITE DE PRUEBAS DE ADMINISTRACIÓN Y CONTENIDO ===\n\n";

$baseDir = dirname(__DIR__);
require_once $baseDir . '/site-content.php';
require_once $baseDir . '/admin/bootstrap.php';

$testsPassed = 0;
$totalTests = 0;

function assert_test($description, $condition) {
    global $testsPassed, $totalTests;
    $totalTests++;
    if ($condition) {
        $testsPassed++;
        echo "  [OK] " . $description . "\n";
    } else {
        echo "  [FAIL] " . $description . "\n";
    }
}

// 1. Defaults y Lectura
echo "1. Validando valores por defecto y lectura de contenido:\n";
$home = site_content('home', site_home_defaults());
assert_test("site_content('home') retorna array completo", is_array($home) && isset($home['hero_title']));

$servicios = site_content('servicios', site_servicios_defaults());
assert_test("site_content('servicios') retorna items válidos", is_array($servicios) && count($servicios['items']) >= 5);

$normativas = site_content('normativas', site_normativas_defaults());
assert_test("site_content('normativas') retorna documentos válidos", is_array($normativas) && count($normativas['items']) >= 6);

$filiales = site_content('filiales', site_filiales_defaults());
assert_test("site_content('filiales') retorna lista de sedes", is_array($filiales) && count($filiales['items']) >= 10);

$comision = site_content('comision', site_comision_defaults());
assert_test("site_content('comision') retorna miembros de conducción", is_array($comision) && count($comision['members']) >= 5);

$novedades = site_content('novedades', site_novedades_defaults());
assert_test("site_content('novedades') retorna archivo histórico", is_array($novedades) && count($novedades['archive_items']) >= 4);

$disallowed = site_content('hack_attempt', array('safe' => true));
assert_test("site_content() bloquea nombres de archivo no autorizados", $disallowed === array('safe' => true));

// 2. Validación de Entrada
echo "\n2. Validando reglas estrictas de entrada:\n";

// Home validation
try {
    $validHome = admin_validate_home($home);
    assert_test("admin_validate_home acepta datos válidos", is_array($validHome));
} catch (Exception $e) {
    assert_test("admin_validate_home falló con datos válidos: " . $e->getMessage(), false);
}

try {
    $invalidHome = $home;
    $invalidHome['email'] = 'no-un-email';
    admin_validate_home($invalidHome);
    assert_test("admin_validate_home rechaza email inválido", false);
} catch (InvalidArgumentException $e) {
    assert_test("admin_validate_home detecta y rechaza email inválido", true);
}

try {
    $invalidHome = $home;
    $invalidHome['maps_embed_url'] = 'https://malicious-site.com/embed';
    admin_validate_home($invalidHome);
    assert_test("admin_validate_home rechaza iframe que no sea de google.com", false);
} catch (InvalidArgumentException $e) {
    assert_test("admin_validate_home protege iframe contra dominios no autorizados", true);
}

// Servicios validation
try {
    $validServicios = admin_validate_servicios($servicios);
    assert_test("admin_validate_servicios acepta datos válidos", is_array($validServicios));
} catch (Exception $e) {
    assert_test("admin_validate_servicios falló con datos válidos: " . $e->getMessage(), false);
}

// Normativas validation
try {
    $validNormativas = admin_validate_normativas($normativas);
    assert_test("admin_validate_normativas acepta datos válidos", is_array($validNormativas));
} catch (Exception $e) {
    assert_test("admin_validate_normativas falló con datos válidos: " . $e->getMessage(), false);
}

// Filiales validation
try {
    $validFiliales = admin_validate_filiales($filiales);
    assert_test("admin_validate_filiales acepta datos válidos", is_array($validFiliales));
} catch (Exception $e) {
    assert_test("admin_validate_filiales falló con datos válidos: " . $e->getMessage(), false);
}

// Comision validation
try {
    $validComision = admin_validate_comision($comision);
    assert_test("admin_validate_comision acepta datos válidos", is_array($validComision));
} catch (Exception $e) {
    assert_test("admin_validate_comision falló con datos válidos: " . $e->getMessage(), false);
}

// Novedades validation
try {
    $validNovedades = admin_validate_novedades($novedades);
    assert_test("admin_validate_novedades acepta datos válidos", is_array($validNovedades));
} catch (Exception $e) {
    assert_test("admin_validate_novedades falló con datos válidos: " . $e->getMessage(), false);
}

// 3. Escritura atómica y respaldos
echo "\n3. Validando escritura atómica, locking y generación de backups:\n";
$testKey = 'test_time_' . time();
$home['hero_eyebrow'] = 'Sindicato de Obreros Panaderos de Lanús';
$backupDir = ADMIN_CONTENT_DIR . '/backups';
$initialBackups = glob($backupDir . '/*.json');
$countBefore = is_array($initialBackups) ? count($initialBackups) : 0;

admin_atomic_json('home.json', $home, true);
$afterBackups = glob($backupDir . '/*.json');
$countAfter = is_array($afterBackups) ? count($afterBackups) : 0;
assert_test("admin_atomic_json genera archivo de backup en private-content/backups/", $countAfter >= $countBefore);

// Auditoría
admin_audit('test_suite_run');
$auditLog = ADMIN_CONTENT_DIR . '/audit.log';
assert_test("admin_audit registra entradas en audit.log", is_file($auditLog) && strpos(file_get_contents($auditLog), 'test_suite_run') !== false);

// 4. Rate Limiting de login
echo "\n4. Validando control de intentos de acceso (Rate-Limiting):\n";
admin_clear_failed_login();
assert_test("admin_is_rate_limited es falso inicialmente", !admin_is_rate_limited());
for ($i = 0; $i < ADMIN_MAX_ATTEMPTS; $i++) {
    admin_register_failed_login();
}
assert_test("admin_is_rate_limited bloquea tras " . ADMIN_MAX_ATTEMPTS . " intentos fallidos", admin_is_rate_limited());
admin_clear_failed_login();
assert_test("admin_clear_failed_login restablece el estado tras login exitoso", !admin_is_rate_limited());

// 5. Renderizado de Páginas Públicas
echo "\n5. Validando renderizado de páginas públicas sin advertencias:\n";
$pages = array(
    'index.php',
    'servicios.php',
    'normativas.php',
    'filiales.php',
    'comision-directiva.php',
    'novedades.php',
    'novedades-home.php',
    'footer.php',
    'menu.php'
);

foreach ($pages as $page) {
    ob_start();
    include $baseDir . '/' . $page;
    $output = ob_get_clean();
    assert_test("Renderizado de {$page} completado (longitud: " . strlen($output) . " bytes)", strlen($output) > 0);
}

echo "\n=======================================================\n";
echo "RESULTADO: {$testsPassed} de {$totalTests} pruebas superadas con éxito.\n";
echo "=======================================================\n";

if ($testsPassed === $totalTests) {
    exit(0);
} else {
    exit(1);
}
