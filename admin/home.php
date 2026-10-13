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
            <h2 class="editor-section-title"><span>02</span> Escala salarial, trámites y redes</h2>
            <div class="form-grid">
                <?php admin_field('salary_url', 'URL de la escala salarial vigente', $content, 'url', 300); ?>
                <?php admin_field('register_url', 'URL de registro para contadores (SP-Online)', $content, 'url', 300); ?>
                <?php admin_field('login_url', 'URL de ingreso para contadores (login SP-Online)', $content, 'url', 300); ?>
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
                <?php admin_field('phone_label', 'Teléfono visible', $content, 'text', 50); ?>
                <?php admin_field('email', 'Correo electrónico', $content, 'email', 45); ?>
                <?php admin_field('maps_url', 'URL para abrir Google Maps', $content, 'url', 400); ?>
                <?php admin_field('maps_embed_url', 'URL del mapa embebido', $content, 'url', 400, 'Solo se aceptan enlaces HTTPS de www.google.com.'); ?>
            </div>
        </section>

        <section class="editor-section">
            <h2 class="editor-section-title"><span>04</span> Noticia destacada en portada</h2>
            <div style="margin-bottom: 18px;">
                <label class="checkbox-field" style="display: inline-flex; align-items: center; gap: 10px; cursor: pointer; padding: 12px 16px; background: #fff; border: 1px solid var(--line);">
                    <input type="checkbox" name="featured_news_enabled" value="1" <?php echo !empty($content['featured_news_enabled']) ? 'checked' : ''; ?> style="width: 18px; height: 18px; cursor: pointer; margin: 0;">
                    <span style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--ink);">Mostrar noticia destacada en la portada del sitio</span>
                </label>
            </div>
            <div class="form-grid">
                <?php admin_field('featured_news_badge', 'Etiqueta o antetítulo', $content, 'text', 50, 'Ej: En desarrollo · Próximo lanzamiento'); ?>
                <?php admin_field('featured_news_title', 'Título de la noticia', $content, 'text', 100); ?>
            </div>
            <?php admin_field('featured_news_description', 'Descripción de la noticia', $content, 'textarea', 800); ?>
            <div class="form-grid">
                <?php admin_field('featured_news_link_url', 'Ruta o URL del enlace (opcional)', $content, 'text', 200, 'Ej: servicios.php o https://...', false); ?>
                <?php admin_field('featured_news_link_label', 'Texto del botón', $content, 'text', 40, 'Ej: Conocer beneficios vigentes'); ?>
            </div>

            <div style="margin-top: 20px;">
                <p style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; color: var(--ink); margin-bottom: 12px;">Imágenes de la noticia (hasta 3 imágenes)</p>
                <div class="form-grid--3">
                    <?php for ($i = 1; $i <= 3; $i++) {
                        $imgKey = 'featured_news_image_' . $i;
                        $imgVal = isset($content[$imgKey]) ? $content[$imgKey] : '';
                    ?>
                    <div class="image-picker-field" data-folder="novedades">
                        <div class="admin-field-head">
                            <span>Imagen #<?php echo $i; ?><?php echo $i === 1 ? ' (Principal)' : ' (Opcional)'; ?></span>
                            <span class="admin-field-limit">máx. 200 car.</span>
                        </div>
                        <div class="image-picker-wrap">
                            <div class="image-preview-thumb image-preview-thumb--rect">
                                <img src="../<?php echo site_escape($imgVal !== '' ? $imgVal : 'images/periodico.jpg'); ?>" alt="Previsualización" style="<?php echo $imgVal === '' ? 'display:none;' : ''; ?>">
                                <div class="image-preview-empty" style="<?php echo $imgVal === '' ? 'display:flex;' : 'display:none;'; ?>"><span>Sin imagen</span></div>
                            </div>
                            <div class="image-picker-body">
                                <div class="image-picker-actions">
                                    <button type="button" class="image-upload-btn"><i class="fa fa-image"></i> Subir...</button>
                                    <input type="file" class="image-file-input" accept="image/jpeg,image/png,image/webp" style="display:none;">
                                    <input type="text" class="image-path-input" name="<?php echo $imgKey; ?>" value="<?php echo site_escape($imgVal); ?>" maxlength="200" placeholder="Ruta de imagen">
                                    <button type="button" class="btn-clear-image" style="background:transparent;border:1px solid #ccc;padding:4px 8px;font-size:11px;cursor:pointer;">Quitar</button>
                                </div>
                                <span class="image-status-badge"></span>
                            </div>
                        </div>
                    </div>
                    <?php } ?>
                </div>
            </div>
        </section>

        <div class="editor-actions">
            <p>Al guardar se valida el contenido, se crea una copia de respaldo y se publica el archivo de forma atómica.</p>
            <button type="submit">Guardar cambios <span>→</span></button>
        </div>
    </form>
</main>
<?php admin_render_end(); ?>
