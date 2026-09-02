<?php

require_once 'bootstrap.php';
admin_require_login();

$content = site_content('instalaciones', site_instalaciones_defaults());
$message = '';
$error = '';

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        admin_verify_csrf();
        $content = admin_validate_instalaciones($_POST);
        admin_atomic_json('instalaciones.json', $content);
        admin_audit('update_instalaciones');
        $message = 'Los cambios en Galería de Instalaciones fueron guardados correctamente.';
    } catch (Throwable $exception) {
        $error = $exception->getMessage();
        if (isset($_POST['items']) && is_array($_POST['items'])) {
            $content['items'] = $_POST['items'];
        }
        foreach (array('page_label', 'page_title', 'page_intro', 'section_kicker', 'section_title') as $field) {
            if (isset($_POST[$field])) {
                $content[$field] = $_POST[$field];
            }
        }
    }
}

admin_render_start('Editar instalaciones');
?>
<main class="admin-shell">
    <header class="admin-header">
        <a class="admin-mark" href="index.php">Panaderos <span>Administración</span></a>
        <a class="admin-logout" href="logout.php">Cerrar sesión</a>
    </header>
    <nav class="admin-breadcrumb"><a href="index.php">Panel</a><span>/</span> Instalaciones y espacios</nav>
    <section class="editor-heading">
        <div>
            <p>Edición de contenido</p>
            <h1>Nuestras <em>instalaciones.</em></h1>
        </div>
        <a href="../instalaciones.php" target="_blank" rel="noopener noreferrer">Ver página <span>↗</span></a>
    </section>

    <?php if ($message !== '') { ?><p class="admin-success" role="status"><?php echo site_escape($message); ?></p><?php } ?>
    <?php if ($error !== '') { ?><p class="admin-error" role="alert"><?php echo site_escape($error); ?></p><?php } ?>

    <form class="editor-form" method="post" autocomplete="off">
        <input type="hidden" name="csrf_token" value="<?php echo site_escape(admin_csrf_token()); ?>">

        <section class="editor-section">
            <h2 class="editor-section-title"><span>01</span> Cabecera y presentación</h2>
            <div class="form-grid">
                <?php admin_field('page_label', 'Etiqueta superior', $content, 'text', 45); ?>
                <?php admin_field('page_title', 'Título de la página', $content, 'text', 40); ?>
            </div>
            <?php admin_field('page_intro', 'Texto introductorio', $content, 'textarea', 250); ?>
            <div class="form-grid">
                <?php admin_field('section_kicker', 'Antetítulo de sección', $content, 'text', 35); ?>
                <?php admin_field('section_title', 'Título de sección', $content, 'text', 60); ?>
            </div>
        </section>

        <section class="editor-section">
            <h2 class="editor-section-title"><span>02</span> Galería de imágenes y espacios</h2>
            <div id="facilities-container" class="repeater-list" data-array-name="items">
                <?php foreach ($content['items'] as $index => $item) {
                    $imgPath = isset($item['image']) && trim((string)$item['image']) !== '' ? $item['image'] : 'images/instalaciones/instalaciones-01.jpg';
                ?>
                <div class="repeater-item" data-index="<?php echo $index; ?>">
                    <div class="repeater-head">
                        <strong>Fotografía #<span class="item-number"><?php echo $index + 1; ?></span>: <span class="item-title-preview"><?php echo site_escape(isset($item['title']) ? $item['title'] : ''); ?></span></strong>
                        <button type="button" class="btn-remove">Eliminar</button>
                    </div>
                    <label class="admin-field">
                        <div class="admin-field-head">
                            <span>Descripción / Epígrafe de la foto</span>
                            <span class="admin-field-limit">máx. 50 car.</span>
                        </div>
                        <input type="text" name="items[<?php echo $index; ?>][title]" value="<?php echo site_escape(isset($item['title']) ? $item['title'] : ''); ?>" maxlength="50" required>
                    </label>

                    <div class="image-picker-field" data-folder="instalaciones">
                        <div class="admin-field-head">
                            <span>Archivo de imagen</span>
                            <span class="admin-field-limit">máx. 200 car.</span>
                        </div>
                        <div class="image-picker-wrap">
                            <div class="image-preview-thumb image-preview-thumb--rect">
                                <img src="../<?php echo site_escape($imgPath); ?>" alt="Previsualización">
                            </div>
                            <div class="image-picker-body">
                                <div class="image-guideline">
                                    <span><strong>Formato recomendado:</strong> 4:3 o 16:9 Horizontal (800 × 600 px) · Peso máx. 2 MB (JPG, PNG o WebP)</span>
                                </div>
                                <div class="image-picker-actions">
                                    <button type="button" class="image-upload-btn"><i class="fa fa-image"></i> Subir foto...</button>
                                    <input type="file" class="image-file-input" accept="image/jpeg,image/png,image/webp" style="display:none;">
                                    <input type="text" class="image-path-input" name="items[<?php echo $index; ?>][image]" value="<?php echo site_escape($imgPath); ?>" maxlength="200" required>
                                </div>
                                <span class="image-status-badge"></span>
                            </div>
                        </div>
                    </div>
                </div>
                <?php } ?>
            </div>
            <button type="button" class="btn-add" data-action="add-item" data-template="tmpl-facility" data-target="facilities-container">+ Agregar otra fotografía a la galería</button>
        </section>

        <div class="editor-actions">
            <p>Al guardar se valida la información, se crea una copia de respaldo automática y se actualiza el archivo JSON.</p>
            <button type="submit">Guardar cambios <span>→</span></button>
        </div>
    </form>
</main>

<template id="tmpl-facility">
    <div class="repeater-item" data-index="__INDEX__">
        <div class="repeater-head">
            <strong>Fotografía #<span class="item-number">__NUMBER__</span></strong>
            <button type="button" class="btn-remove">Eliminar</button>
        </div>
        <label class="admin-field">
            <div class="admin-field-head">
                <span>Descripción / Epígrafe de la foto</span>
                <span class="admin-field-limit">máx. 50 car.</span>
            </div>
            <input type="text" name="items[__INDEX__][title]" maxlength="50" required>
        </label>
        <div class="image-picker-field" data-folder="instalaciones">
            <div class="admin-field-head">
                <span>Archivo de imagen</span>
                <span class="admin-field-limit">máx. 200 car.</span>
            </div>
            <div class="image-picker-wrap">
                <div class="image-preview-thumb image-preview-thumb--rect">
                    <img src="../images/instalaciones/instalaciones-01.jpg" alt="Previsualización">
                </div>
                <div class="image-picker-body">
                    <div class="image-guideline">
                        <span><strong>Formato recomendado:</strong> 4:3 o 16:9 Horizontal (800 × 600 px) · Peso máx. 2 MB (JPG, PNG o WebP)</span>
                    </div>
                    <div class="image-picker-actions">
                        <button type="button" class="image-upload-btn"><i class="fa fa-image"></i> Subir foto...</button>
                        <input type="file" class="image-file-input" accept="image/jpeg,image/png,image/webp" style="display:none;">
                        <input type="text" class="image-path-input" name="items[__INDEX__][image]" value="images/instalaciones/instalaciones-01.jpg" maxlength="200" required>
                    </div>
                    <span class="image-status-badge"></span>
                </div>
            </div>
        </div>
    </div>
</template>

<?php admin_render_end(); ?>
