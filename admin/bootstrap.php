<?php

require_once dirname(__DIR__) . '/site-content.php';

define('ADMIN_CONTENT_DIR', dirname(__DIR__) . '/private-content');
define('ADMIN_LOGIN_WINDOW', 900);
define('ADMIN_MAX_ATTEMPTS', 5);

function admin_is_https()
{
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443);
}

function admin_start_session()
{
    if (session_status() === PHP_SESSION_ACTIVE || headers_sent()) {
        return;
    }

    ini_set('session.use_only_cookies', '1');
    ini_set('session.use_strict_mode', '1');
    ini_set('session.cookie_httponly', '1');
    session_name('panaderos_admin');
    session_set_cookie_params(array(
        'lifetime' => 0,
        'path' => '/',
        'secure' => admin_is_https(),
        'httponly' => true,
        'samesite' => 'Strict'
    ));
    session_start();
}

function admin_headers()
{
    if (headers_sent() || PHP_SAPI === 'cli') {
        return;
    }

    header('X-Frame-Options: DENY');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: same-origin');
    header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com data:; img-src 'self' data:; base-uri 'self'; form-action 'self'; frame-ancestors 'none'");
}

function admin_configured_hash()
{
    $hash = getenv('PANADEROS_ADMIN_PASSWORD_HASH');
    $localConfig = ADMIN_CONTENT_DIR . '/admin.local.php';
    if ((!is_string($hash) || $hash === '') && is_file($localConfig)) {
        $config = require $localConfig;
        if (is_array($config) && isset($config['password_hash'])) {
            $hash = $config['password_hash'];
        }
    }

    return is_string($hash) && password_get_info($hash)['algo'] !== null ? $hash : null;
}

function admin_csrf_token()
{
    if (empty($_SESSION['admin_csrf'])) {
        $_SESSION['admin_csrf'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['admin_csrf'];
}

function admin_verify_csrf()
{
    $token = isset($_POST['csrf_token']) ? (string) $_POST['csrf_token'] : '';
    if (!isset($_SESSION['admin_csrf']) || !hash_equals($_SESSION['admin_csrf'], $token)) {
        http_response_code(403);
        exit('Solicitud no válida. Volvé a cargar la página e intentá nuevamente.');
    }
}

function admin_client_key()
{
    $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'unknown';
    return hash('sha256', $ip);
}

function admin_attempts()
{
    $path = ADMIN_CONTENT_DIR . '/login-attempts.json';
    if (!is_file($path)) {
        return array();
    }

    $data = json_decode((string) file_get_contents($path), true);
    return is_array($data) ? $data : array();
}

function admin_save_attempts(array $attempts)
{
    admin_atomic_json('login-attempts.json', $attempts, false);
}

function admin_is_rate_limited()
{
    $attempts = admin_attempts();
    $key = admin_client_key();
    if (empty($attempts[$key])) {
        return false;
    }

    $entry = $attempts[$key];
    return $entry['count'] >= ADMIN_MAX_ATTEMPTS && (time() - $entry['last']) < ADMIN_LOGIN_WINDOW;
}

function admin_register_failed_login()
{
    $attempts = admin_attempts();
    $key = admin_client_key();
    $entry = isset($attempts[$key]) ? $attempts[$key] : array('count' => 0, 'last' => 0);
    $entry['count']++;
    $entry['last'] = time();
    $attempts[$key] = $entry;
    admin_save_attempts($attempts);
}

function admin_clear_failed_login()
{
    $attempts = admin_attempts();
    unset($attempts[admin_client_key()]);
    admin_save_attempts($attempts);
}

function admin_atomic_json($filename, array $data, $backup = true)
{
    $allowed = array(
        'home.json',
        'servicios.json',
        'normativas.json',
        'filiales.json',
        'comision.json',
        'novedades.json',
        'login-attempts.json'
    );
    if (!in_array($filename, $allowed, true)) {
        throw new RuntimeException('Destino de escritura no permitido.');
    }

    $path = ADMIN_CONTENT_DIR . '/' . $filename;
    $lock = fopen($path . '.lock', 'c');
    if ($lock === false || !flock($lock, LOCK_EX)) {
        throw new RuntimeException('No fue posible bloquear el archivo de contenido.');
    }

    try {
        if ($backup && is_file($path)) {
            $backupPath = ADMIN_CONTENT_DIR . '/backups/' . basename($filename, '.json') . '-' . gmdate('Ymd-His') . '.json';
            copy($path, $backupPath);
        }

        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            throw new RuntimeException('No fue posible preparar el contenido para guardar.');
        }

        $temporary = $path . '.' . bin2hex(random_bytes(8)) . '.tmp';
        if (file_put_contents($temporary, $json . PHP_EOL, LOCK_EX) === false || !rename($temporary, $path)) {
            @unlink($temporary);
            throw new RuntimeException('No fue posible guardar los cambios.');
        }
    } finally {
        flock($lock, LOCK_UN);
        fclose($lock);
    }
}

function admin_audit($action)
{
    $line = gmdate('c') . "\t" . $action . "\t" . admin_client_key() . PHP_EOL;
    @file_put_contents(ADMIN_CONTENT_DIR . '/audit.log', $line, FILE_APPEND | LOCK_EX);
}

function admin_require_login()
{
    if (empty($_SESSION['admin_logged_in'])) {
        header('Location: login.php');
        exit;
    }

    if (empty($_SESSION['admin_last_activity']) || (time() - $_SESSION['admin_last_activity']) > 1800) {
        $_SESSION = array();
        session_destroy();
        header('Location: login.php?expired=1');
        exit;
    }

    $_SESSION['admin_last_activity'] = time();
}

function admin_text($value, $maximum)
{
    $value = trim(strip_tags((string) $value));
    $value = preg_replace('/\s+/u', ' ', $value);
    if ($value === '' || mb_strlen($value) > $maximum) {
        return null;
    }

    return $value;
}

function admin_multiline($value, $maximum)
{
    $value = trim(strip_tags((string) $value));
    $value = str_replace(array("\r\n", "\r"), "\n", $value);
    $value = preg_replace("/\n{3,}/", "\n\n", $value);
    if ($value === '' || mb_strlen($value) > $maximum) {
        return null;
    }

    return $value;
}

function admin_https_url($value, $maximum = 2000)
{
    $value = trim((string) $value);
    if (mb_strlen($value) > $maximum || !filter_var($value, FILTER_VALIDATE_URL)) {
        return null;
    }

    $parts = parse_url($value);
    return isset($parts['scheme']) && $parts['scheme'] === 'https' ? $value : null;
}

function admin_safe_url($value, $maximum = 2000)
{
    $value = trim((string) $value);
    if ($value === '' || mb_strlen($value) > $maximum) {
        return null;
    }

    if (strpos($value, 'https://') === 0) {
        return admin_https_url($value, $maximum);
    }

    if (strpos($value, '..') !== false || strpos($value, '//') !== false || strpos($value, ':') !== false) {
        return null;
    }

    if (preg_match('/^[a-zA-Z0-9_\-\.\/]+$/', $value)) {
        return ltrim($value, '/');
    }

    return null;
}

function admin_validate_home(array $input)
{
    $limits = array(
        'hero_eyebrow' => 120, 'hero_title' => 80, 'hero_emphasis' => 80, 'hero_after' => 80,
        'hero_lead' => 350, 'hero_quote' => 260, 'hero_quote_author' => 120, 'social_intro' => 450,
        'instagram_label' => 80, 'facebook_label' => 100, 'address' => 180,
        'phone' => 30, 'phone_label' => 60, 'email' => 180
    );
    $content = array();
    foreach ($limits as $field => $maximum) {
        $content[$field] = admin_text(isset($input[$field]) ? $input[$field] : '', $maximum);
        if ($content[$field] === null) {
            throw new InvalidArgumentException('Revisá el campo “' . $field . '”.');
        }
    }

    if (!filter_var($content['email'], FILTER_VALIDATE_EMAIL) || !preg_match('/^[0-9+(). -]+$/', $content['phone'])) {
        throw new InvalidArgumentException('Revisá el correo electrónico y el teléfono.');
    }

    foreach (array('salary_url', 'instagram_url', 'facebook_url', 'maps_url', 'maps_embed_url') as $field) {
        $content[$field] = admin_https_url(isset($input[$field]) ? $input[$field] : '');
        if ($content[$field] === null) {
            throw new InvalidArgumentException('La URL “' . $field . '” debe usar HTTPS y ser válida.');
        }
    }

    $mapHost = parse_url($content['maps_embed_url'], PHP_URL_HOST);
    if ($mapHost !== 'www.google.com') {
        throw new InvalidArgumentException('El mapa embebido debe provenir de www.google.com.');
    }

    return $content;
}

function admin_validate_servicios(array $input)
{
    $limits = array(
        'page_label' => 100, 'page_title' => 100, 'page_intro' => 400,
        'section_kicker' => 80, 'section_title' => 120,
        'callout_kicker' => 80, 'callout_title' => 200, 'callout_action_text' => 80, 'callout_email' => 180
    );
    $content = array();
    foreach ($limits as $field => $maximum) {
        $content[$field] = admin_text(isset($input[$field]) ? $input[$field] : '', $maximum);
        if ($content[$field] === null) {
            throw new InvalidArgumentException('Revisá el campo “' . $field . '”.');
        }
    }

    if (!filter_var($content['callout_email'], FILTER_VALIDATE_EMAIL)) {
        throw new InvalidArgumentException('El correo de contacto en servicios no es válido.');
    }

    $rawItems = isset($input['items']) && is_array($input['items']) ? $input['items'] : array();
    if (empty($rawItems)) {
        throw new InvalidArgumentException('Debe haber al menos un beneficio cargado.');
    }

    $items = array();
    foreach ($rawItems as $item) {
        if (!is_array($item)) {
            continue;
        }
        $title = admin_text(isset($item['title']) ? $item['title'] : '', 80);
        $desc = admin_text(isset($item['description']) ? $item['description'] : '', 300);
        $detail = admin_multiline(isset($item['detail']) ? $item['detail'] : '', 300);
        $icon = isset($item['icon']) ? trim(strip_tags((string) $item['icon'])) : 'fa-check';
        if (!preg_match('/^fa-[a-z0-9\-]+$/', $icon)) {
            $icon = 'fa-check';
        }
        $theme = isset($item['theme']) && in_array($item['theme'], array('ink', 'sun', '')) ? $item['theme'] : '';

        if ($title === null || $desc === null || $detail === null) {
            throw new InvalidArgumentException('Todos los campos de cada servicio son obligatorios.');
        }

        $items[] = array(
            'icon' => $icon,
            'theme' => $theme,
            'title' => $title,
            'description' => $desc,
            'detail' => $detail
        );
    }

    $content['items'] = $items;
    return $content;
}

function admin_validate_normativas(array $input)
{
    $limits = array(
        'page_label' => 100, 'page_title' => 100, 'page_intro' => 400,
        'section_kicker' => 80, 'section_title' => 120,
        'note_text' => 400, 'note_action_text' => 80, 'note_email' => 180
    );
    $content = array();
    foreach ($limits as $field => $maximum) {
        $content[$field] = admin_text(isset($input[$field]) ? $input[$field] : '', $maximum);
        if ($content[$field] === null) {
            throw new InvalidArgumentException('Revisá el campo “' . $field . '”.');
        }
    }

    if (!filter_var($content['note_email'], FILTER_VALIDATE_EMAIL)) {
        throw new InvalidArgumentException('El correo de consulta no es válido.');
    }

    $rawItems = isset($input['items']) && is_array($input['items']) ? $input['items'] : array();
    if (empty($rawItems)) {
        throw new InvalidArgumentException('Debe haber al menos un documento cargado.');
    }

    $items = array();
    foreach ($rawItems as $item) {
        if (!is_array($item)) {
            continue;
        }
        $kicker = admin_text(isset($item['kicker']) ? $item['kicker'] : '', 80);
        $title = admin_multiline(isset($item['title']) ? $item['title'] : '', 100);
        $url = admin_safe_url(isset($item['url']) ? $item['url'] : '', 500);
        $isFeatured = !empty($item['is_featured']);

        if ($kicker === null || $title === null || $url === null) {
            throw new InvalidArgumentException('Revisá los títulos, categorías y enlaces de los documentos.');
        }

        $items[] = array(
            'kicker' => $kicker,
            'title' => $title,
            'url' => $url,
            'is_featured' => $isFeatured
        );
    }

    $content['items'] = $items;
    return $content;
}

function admin_validate_filiales(array $input)
{
    $limits = array(
        'page_label' => 100, 'page_title' => 100, 'page_intro' => 400,
        'section_kicker' => 80, 'section_title' => 120
    );
    $content = array();
    foreach ($limits as $field => $maximum) {
        $content[$field] = admin_text(isset($input[$field]) ? $input[$field] : '', $maximum);
        if ($content[$field] === null) {
            throw new InvalidArgumentException('Revisá el campo “' . $field . '”.');
        }
    }

    $rawItems = isset($input['items']) && is_array($input['items']) ? $input['items'] : array();
    if (empty($rawItems)) {
        throw new InvalidArgumentException('Debe haber al menos una filial en el directorio.');
    }

    $items = array();
    foreach ($rawItems as $item) {
        if (!is_array($item)) {
            continue;
        }
        $name = admin_text(isset($item['name']) ? $item['name'] : '', 100);
        $address = admin_text(isset($item['address']) ? $item['address'] : '', 180);
        $phone = admin_text(isset($item['phone']) ? $item['phone'] : '', 100);
        $secretary = admin_text(isset($item['secretary']) ? $item['secretary'] : '', 100);

        if ($name === null || $address === null || $phone === null || $secretary === null) {
            throw new InvalidArgumentException('Todos los campos de cada filial deben estar completos.');
        }

        $items[] = array(
            'name' => $name,
            'address' => $address,
            'phone' => $phone,
            'secretary' => $secretary
        );
    }

    $content['items'] = $items;
    return $content;
}

function admin_validate_comision(array $input)
{
    $limits = array(
        'page_label' => 100, 'page_title' => 100, 'page_intro' => 400,
        'section_kicker' => 80, 'section_title' => 120
    );
    $content = array();
    foreach ($limits as $field => $maximum) {
        $content[$field] = admin_text(isset($input[$field]) ? $input[$field] : '', $maximum);
        if ($content[$field] === null) {
            throw new InvalidArgumentException('Revisá el campo “' . $field . '”.');
        }
    }

    $rawMembers = isset($input['members']) && is_array($input['members']) ? $input['members'] : array();
    if (empty($rawMembers)) {
        throw new InvalidArgumentException('Debe haber al menos un integrante de la comisión.');
    }

    $members = array();
    foreach ($rawMembers as $member) {
        if (!is_array($member)) {
            continue;
        }
        $name = admin_text(isset($member['name']) ? $member['name'] : '', 100);
        $role = admin_text(isset($member['role']) ? $member['role'] : '', 100);
        $photo = admin_safe_url(isset($member['photo']) && trim((string) $member['photo']) !== '' ? $member['photo'] : 'images/directivos/sin-foto.jpg', 300);

        if ($name === null || $role === null || $photo === null) {
            throw new InvalidArgumentException('Revisá los nombres, cargos y fotos de la comisión.');
        }

        $members[] = array(
            'name' => $name,
            'role' => $role,
            'photo' => $photo
        );
    }

    $content['members'] = $members;
    return $content;
}

function admin_validate_novedades(array $input)
{
    $limits = array(
        'page_label' => 100, 'page_title' => 100, 'page_intro' => 400,
        'social_kicker' => 80, 'social_title' => 120, 'social_intro' => 400,
        'archive_kicker' => 80, 'archive_title' => 120, 'archive_intro' => 400
    );
    $content = array();
    foreach ($limits as $field => $maximum) {
        $content[$field] = admin_text(isset($input[$field]) ? $input[$field] : '', $maximum);
        if ($content[$field] === null) {
            throw new InvalidArgumentException('Revisá el campo “' . $field . '”.');
        }
    }

    $rawArchive = isset($input['archive_items']) && is_array($input['archive_items']) ? $input['archive_items'] : array();
    $archiveItems = array();
    foreach ($rawArchive as $item) {
        if (!is_array($item)) {
            continue;
        }
        $title = admin_text(isset($item['title']) ? $item['title'] : '', 150);
        $tag = admin_text(isset($item['tag']) ? $item['tag'] : 'Archivo', 40);
        $url = admin_safe_url(isset($item['url']) ? $item['url'] : '', 500);
        $image = admin_safe_url(isset($item['image']) ? $item['image'] : '', 500);
        $alt = admin_text(isset($item['alt']) ? $item['alt'] : '', 150);

        if ($title === null || $tag === null || $url === null || $image === null || $alt === null) {
            throw new InvalidArgumentException('Revisá los campos de los artículos del archivo histórico.');
        }

        $archiveItems[] = array(
            'title' => $title,
            'tag' => $tag,
            'url' => $url,
            'image' => $image,
            'alt' => $alt
        );
    }

    $content['archive_items'] = $archiveItems;
    return $content;
}

function admin_field($name, $label, array $content, $type = 'text', $maxlength = null, $hint = '')
{
    $value = isset($content[$name]) ? $content[$name] : '';
    $maxAttr = $maxlength !== null ? ' maxlength="' . (int) $maxlength . '"' : '';
    ?>
    <label class="admin-field" for="<?php echo site_escape($name); ?>">
        <div class="admin-field-head">
            <span><?php echo site_escape($label); ?></span>
            <?php if ($maxlength !== null) { ?>
                <span class="admin-field-limit">máx. <?php echo (int) $maxlength; ?> car.</span>
            <?php } ?>
        </div>
        <?php if ($type === 'textarea') { ?>
            <textarea id="<?php echo site_escape($name); ?>" name="<?php echo site_escape($name); ?>" rows="3"<?php echo $maxAttr; ?> required><?php echo site_escape($value); ?></textarea>
        <?php } else { ?>
            <input id="<?php echo site_escape($name); ?>" name="<?php echo site_escape($name); ?>" type="<?php echo site_escape($type); ?>" value="<?php echo site_escape($value); ?>"<?php echo $maxAttr; ?> required>
        <?php } ?>
        <?php if ($hint !== '') { ?><small><?php echo site_escape($hint); ?></small><?php } ?>
    </label>
    <?php
}

function admin_render_start($title)
{
    ?><!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title><?php echo site_escape($title); ?> · Administración</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&display=block" rel="stylesheet">
    <link href="../css/font-awesome.min.css" rel="stylesheet">
    <link href="admin.css" rel="stylesheet">
</head>
<body>
<?php
}

function admin_render_end()
{
    ?><script src="admin.js"></script></body>
</html><?php
}

admin_headers();
admin_start_session();

