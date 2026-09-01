<?php

require_once 'bootstrap.php';

if (!empty($_SESSION['admin_logged_in'])) {
    header('Location: index.php');
    exit;
}

$message = '';
$hash = admin_configured_hash();
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST' && $hash !== null) {
    admin_verify_csrf();
    if (admin_is_rate_limited()) {
        $message = 'Demasiados intentos. Esperá 15 minutos antes de volver a intentar.';
    } elseif (password_verify(isset($_POST['password']) ? (string) $_POST['password'] : '', $hash)) {
        session_regenerate_id(true);
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_last_activity'] = time();
        try {
            admin_clear_failed_login();
            admin_audit('login');
        } catch (Throwable $ignored) {
            // Continuar con la redirección si la autenticación fue válida
        }
        header('Location: index.php');
        exit;
    } else {
        admin_register_failed_login();
        $message = 'La contraseña no es válida.';
    }
}

admin_render_start('Acceso');
?>
<main class="login-shell"><section class="login-panel"><p class="admin-kicker">Acceso restringido</p><h1>Administración<br><em>Panaderos Lanús.</em></h1>
<?php if ($hash === null) { ?>
    <div class="admin-notice"><strong>Panel pendiente de configuración.</strong><p>Creá <code>private-content/admin.local.php</code> desde el archivo de ejemplo e incorporá un hash de contraseña. No uses una contraseña en texto plano.</p></div>
<?php } else { ?>
    <?php if ($message !== '') { ?><p class="admin-error" role="alert"><?php echo site_escape($message); ?></p><?php } ?>
    <form method="post" autocomplete="off"><input type="hidden" name="csrf_token" value="<?php echo site_escape(admin_csrf_token()); ?>"><label for="password">Contraseña</label><input id="password" name="password" type="password" minlength="14" required autofocus><button type="submit">Ingresar <span>→</span></button></form>
<?php } ?>
    <a class="admin-return" href="../index.php">Volver al sitio público</a>
</section></main>
<?php admin_render_end(); ?>
