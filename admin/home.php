<?php

require_once 'bootstrap.php';
admin_require_login();

$content = site_content('home', site_home_defaults());
$message = '';
$error = '';

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        admin_verify_csrf();
        $content = admin_validate_home($_POST);
        admin_atomic_json('home.json', $content);
        admin_audit('update_home');
        $message = 'Los cambios fueron guardados y hay una copia de respaldo disponible.';
    } catch (Throwable $exception) {
        $error = $exception->getMessage();
        $content = array_merge($content, $_POST);
    }
}

admin_render_start('Editar home');
?>
<main class="admin-shell">
    <?php admin_header_html(); ?>
    <nav class="admin-breadcrumb"><a href="index.php">Panel</a><span>/</span> Home y contacto</nav>
    <section class="editor-heading"><div><p>Edición de contenido</p><h1>Home y <em>contacto.</em></h1></div><a href="../index.php" target="_blank" rel="noopener noreferrer">Ver sitio <span>↗</span></a></section>
    <?php if ($message !== '') { ?><p class="admin-success" role="status"><?php echo site_escape($message); ?></p><?php } ?>
    <?php if ($error !== '') { ?><p class="admin-error" role="alert"><?php echo site_escape($error); ?></p><?php } ?>
    <form class="editor-form" method="post" autocomplete="off">
        <input type="hidden" name="csrf_token" value="<?php echo site_escape(admin_csrf_token()); ?>">
        
        <section class="editor-section">
            <h2 class="editor-section-title"><span>01</span> Portada</h2>
            <div class="form-grid">
                <?php admin_field('hero_eyebrow', 'Antetítulo', $content, 'text', 60); ?>
                <?php admin_field('hero_title', 'Título principal', $content, 'text', 25); ?>
                <?php admin_field('hero_emphasis', 'Palabra en cursiva', $content, 'text', 20); ?>
                <?php admin_field('hero_after', 'Cierre del título', $content, 'text', 20); ?>
            </div>
            <?php admin_field('hero_lead', 'Texto de presentación', $content, 'textarea', 200); ?>
            <div class="form-grid">
                <?php admin_field('hero_quote', 'Cita', $content, 'textarea', 140); ?>
                <?php admin_field('hero_quote_author', 'Autor de la cita', $content, 'text', 35); ?>
            </div>
        </section>

        <section class="editor-section">
            <h2 class="editor-section-title"><span>02</span> Escala salarial y redes</h2>
            <div class="form-grid">
                <?php admin_field('salary_url', 'URL de la escala salarial vigente', $content, 'url', 300); ?>
                <?php admin_field('instagram_url', 'URL de Instagram', $content, 'url', 300); ?>
                <?php admin_field('instagram_label', 'Nombre visible en Instagram', $content, 'text', 25); ?>
                <?php admin_field('facebook_url', 'URL de Facebook', $content, 'url', 300); ?>
                <?php admin_field('facebook_label', 'Nombre visible en Facebook', $content, 'text', 30); ?>
            </div>
            <?php admin_field('social_intro', 'Texto del bloque de canales oficiales', $content, 'textarea', 200); ?>
        </section>

        <section class="editor-section">
            <h2 class="editor-section-title"><span>03</span> Sede y contacto</h2>
            <div class="form-grid">
                <?php admin_field('address', 'Dirección visible', $content, 'text', 50); ?>
                <?php admin_field('phone', 'Teléfono para llamar', $content, 'tel', 25, 'Solo números, espacios y los símbolos + ( ) . -'); ?>
                <?php admin_field('phone_label', 'Teléfono visible', $content, 'text', 30); ?>
                <?php admin_field('email', 'Correo electrónico', $content, 'email', 45); ?>
                <?php admin_field('maps_url', 'URL para abrir Google Maps', $content, 'url', 400); ?>
                <?php admin_field('maps_embed_url', 'URL del mapa embebido', $content, 'url', 400, 'Solo se aceptan enlaces HTTPS de www.google.com.'); ?>
            </div>
        </section>

        <div class="editor-actions">
            <p>Al guardar se valida el contenido, se crea una copia de respaldo y se publica el archivo de forma atómica.</p>
            <button type="submit">Guardar cambios <span>→</span></button>
        </div>
    </form>
</main>
<?php admin_render_end(); ?>
