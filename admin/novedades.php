<?php

require_once 'bootstrap.php';
admin_require_login();

$content = site_content('novedades', site_novedades_defaults());
$message = '';
$error = '';

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        admin_verify_csrf();
        $content = admin_validate_novedades($_POST);
        admin_atomic_json('novedades.json', $content);
        admin_audit('update_novedades');
        $message = 'Los cambios en Novedades y Archivo fueron guardados correctamente.';
    } catch (Throwable $exception) {
        $error = $exception->getMessage();
        if (isset($_POST['archive_items']) && is_array($_POST['archive_items'])) {
            $content['archive_items'] = $_POST['archive_items'];
        }
        foreach (array('page_label', 'page_title', 'page_intro', 'social_kicker', 'social_title', 'social_intro', 'archive_kicker', 'archive_title', 'archive_intro') as $field) {
            if (isset($_POST[$field])) {
                $content[$field] = $_POST[$field];
            }
        }
    }
}

admin_render_start('Editar novedades y archivo');
?>
<main class="admin-shell">
    <header class="admin-header">
        <a class="admin-mark" href="index.php">Panaderos <span>Administración</span></a>
        <a class="admin-logout" href="logout.php">Cerrar sesión</a>
    </header>
    <nav class="admin-breadcrumb"><a href="index.php">Panel</a><span>/</span> Novedades y archivo</nav>
    <section class="editor-heading">
        <div>
            <p>Edición de contenido</p>
            <h1>Novedades y <em>archivo.</em></h1>
        </div>
        <a href="../novedades.php" target="_blank" rel="noopener noreferrer">Ver página <span>↗</span></a>
    </section>

    <?php if ($message !== '') { ?><p class="admin-success" role="status"><?php echo site_escape($message); ?></p><?php } ?>
    <?php if ($error !== '') { ?><p class="admin-error" role="alert"><?php echo site_escape($error); ?></p><?php } ?>

    <form class="editor-form" method="post" autocomplete="off">
        <input type="hidden" name="csrf_token" value="<?php echo site_escape(admin_csrf_token()); ?>">

        <section class="editor-section">
            <h2 class="editor-section-title"><span>01</span> Cabecera de la sección</h2>
            <div class="form-grid">
                <?php admin_field('page_label', 'Etiqueta superior', $content, 'text', 100); ?>
                <?php admin_field('page_title', 'Título de la página', $content, 'text', 100); ?>
            </div>
            <?php admin_field('page_intro', 'Texto introductorio', $content, 'textarea', 400); ?>
        </section>

        <section class="editor-section">
            <h2 class="editor-section-title"><span>02</span> Canales oficiales de actualidad</h2>
            <div class="form-grid">
                <?php admin_field('social_kicker', 'Antetítulo de redes', $content, 'text', 80); ?>
                <?php admin_field('social_title', 'Título destacado', $content, 'text', 120); ?>
            </div>
            <?php admin_field('social_intro', 'Texto descriptivo', $content, 'textarea', 400); ?>
        </section>

        <section class="editor-section">
            <h2 class="editor-section-title"><span>03</span> Artículos del archivo institucional</h2>
            <div class="form-grid">
                <?php admin_field('archive_kicker', 'Antetítulo del archivo', $content, 'text', 80); ?>
                <?php admin_field('archive_title', 'Título del archivo', $content, 'text', 120); ?>
            </div>
            <?php admin_field('archive_intro', 'Texto de presentación del archivo', $content, 'textarea', 400); ?>

            <div id="archive-container" class="repeater-list mt-20" data-array-name="archive_items">
                <?php foreach ($content['archive_items'] as $index => $item) { ?>
                <div class="repeater-item" data-index="<?php echo $index; ?>">
                    <div class="repeater-head">
                        <strong>Artículo #<span class="item-number"><?php echo $index + 1; ?></span>: <span class="item-title-preview"><?php echo site_escape(isset($item['title']) ? $item['title'] : ''); ?></span></strong>
                        <button type="button" class="btn-remove">Eliminar</button>
                    </div>
                    <div class="form-grid">
                        <label class="admin-field">
                            <div class="admin-field-head">
                                <span>Título del artículo</span>
                                <span class="admin-field-limit">máx. 150</span>
                            </div>
                            <input type="text" name="archive_items[<?php echo $index; ?>][title]" value="<?php echo site_escape(isset($item['title']) ? $item['title'] : ''); ?>" maxlength="150" required>
                        </label>
                        <label class="admin-field">
                            <div class="admin-field-head">
                                <span>Etiqueta visible</span>
                                <span class="admin-field-limit">máx. 40</span>
                            </div>
                            <input type="text" name="archive_items[<?php echo $index; ?>][tag]" value="<?php echo site_escape(isset($item['tag']) ? $item['tag'] : 'Archivo'); ?>" maxlength="40" required>
                        </label>
                    </div>
                    <div class="form-grid--3 mt-14">
                        <label class="admin-field">
                            <div class="admin-field-head">
                                <span>Ruta o URL del enlace</span>
                                <span class="admin-field-limit">máx. 500</span>
                            </div>
                            <input type="text" name="archive_items[<?php echo $index; ?>][url]" value="<?php echo site_escape(isset($item['url']) ? $item['url'] : ''); ?>" maxlength="500" required>
                        </label>
                        <label class="admin-field">
                            <div class="admin-field-head">
                                <span>Ruta de la imagen</span>
                                <span class="admin-field-limit">máx. 500</span>
                            </div>
                            <input type="text" name="archive_items[<?php echo $index; ?>][image]" value="<?php echo site_escape(isset($item['image']) ? $item['image'] : ''); ?>" maxlength="500" required>
                        </label>
                        <label class="admin-field">
                            <div class="admin-field-head">
                                <span>Texto alternativo (alt)</span>
                                <span class="admin-field-limit">máx. 150</span>
                            </div>
                            <input type="text" name="archive_items[<?php echo $index; ?>][alt]" value="<?php echo site_escape(isset($item['alt']) ? $item['alt'] : ''); ?>" maxlength="150" required>
                        </label>
                    </div>
                </div>
                <?php } ?>
            </div>
            <button type="button" class="btn-add" data-action="add-item" data-template="tmpl-archive" data-target="archive-container">+ Agregar otro artículo al archivo</button>
        </section>

        <div class="editor-actions">
            <p>Al guardar se valida la información, se crea una copia de respaldo automática y se actualiza el archivo JSON.</p>
            <button type="submit">Guardar cambios <span>→</span></button>
        </div>
    </form>
</main>

<template id="tmpl-archive">
    <div class="repeater-item" data-index="__INDEX__">
        <div class="repeater-head">
            <strong>Artículo #<span class="item-number">__NUMBER__</span></strong>
            <button type="button" class="btn-remove">Eliminar</button>
        </div>
        <div class="form-grid">
            <label class="admin-field">
                <div class="admin-field-head">
                    <span>Título del artículo</span>
                    <span class="admin-field-limit">máx. 150</span>
                </div>
                <input type="text" name="archive_items[__INDEX__][title]" maxlength="150" required>
            </label>
            <label class="admin-field">
                <div class="admin-field-head">
                    <span>Etiqueta visible</span>
                    <span class="admin-field-limit">máx. 40</span>
                </div>
                <input type="text" name="archive_items[__INDEX__][tag]" value="Archivo" maxlength="40" required>
            </label>
        </div>
        <div class="form-grid--3 mt-14">
            <label class="admin-field">
                <div class="admin-field-head">
                    <span>Ruta o URL del enlace</span>
                    <span class="admin-field-limit">máx. 500</span>
                </div>
                <input type="text" name="archive_items[__INDEX__][url]" maxlength="500" required>
            </label>
            <label class="admin-field">
                <div class="admin-field-head">
                    <span>Ruta de la imagen</span>
                    <span class="admin-field-limit">máx. 500</span>
                </div>
                <input type="text" name="archive_items[__INDEX__][image]" maxlength="500" required>
            </label>
            <label class="admin-field">
                <div class="admin-field-head">
                    <span>Texto alternativo (alt)</span>
                    <span class="admin-field-limit">máx. 150</span>
                </div>
                <input type="text" name="archive_items[__INDEX__][alt]" maxlength="150" required>
            </label>
        </div>
    </div>
</template>

<?php admin_render_end(); ?>
