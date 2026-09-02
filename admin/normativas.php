<?php

require_once 'bootstrap.php';
admin_require_login();

$content = site_content('normativas', site_normativas_defaults());
$message = '';
$error = '';

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        admin_verify_csrf();
        $content = admin_validate_normativas($_POST);
        admin_atomic_json('normativas.json', $content);
        admin_audit('update_normativas');
        $message = 'Los cambios en Normativas y Documentos fueron guardados correctamente.';
    } catch (Throwable $exception) {
        $error = $exception->getMessage();
        if (isset($_POST['items']) && is_array($_POST['items'])) {
            $content['items'] = $_POST['items'];
        }
        foreach (array('page_label', 'page_title', 'page_intro', 'section_kicker', 'section_title', 'note_text', 'note_action_text', 'note_email') as $field) {
            if (isset($_POST[$field])) {
                $content[$field] = $_POST[$field];
            }
        }
    }
}

admin_render_start('Editar normativas y documentos');
?>
<main class="admin-shell">
    <header class="admin-header">
        <a class="admin-mark" href="index.php">Panaderos <span>Administración</span></a>
        <a class="admin-logout" href="logout.php">Cerrar sesión</a>
    </header>
    <nav class="admin-breadcrumb"><a href="index.php">Panel</a><span>/</span> Normativas y documentos</nav>
    <section class="editor-heading">
        <div>
            <p>Edición de contenido</p>
            <h1>Normativas y <em>documentos.</em></h1>
        </div>
        <a href="../normativas.php" target="_blank" rel="noopener noreferrer">Ver página <span>↗</span></a>
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
            <h2 class="editor-section-title"><span>02</span> Documentos y enlaces normativos</h2>
            <div id="items-container" class="repeater-list" data-array-name="items">
                <?php foreach ($content['items'] as $index => $item) { ?>
                <div class="repeater-item" data-index="<?php echo $index; ?>">
                    <div class="repeater-head">
                        <strong>Documento #<span class="item-number"><?php echo $index + 1; ?></span></strong>
                        <button type="button" class="btn-remove">Eliminar</button>
                    </div>
                    <div class="form-grid">
                        <label class="admin-field">
                            <div class="admin-field-head">
                                <span>Categoría / Antetítulo</span>
                                <span class="admin-field-limit">máx. 35 car.</span>
                            </div>
                            <input type="text" name="items[<?php echo $index; ?>][kicker]" value="<?php echo site_escape(isset($item['kicker']) ? $item['kicker'] : ''); ?>" maxlength="35" required>
                        </label>
                        <label class="admin-field">
                            <div class="admin-field-head">
                                <span>Título visible (ej. CCT 231/94)</span>
                                <span class="admin-field-limit">máx. 30 car.</span>
                            </div>
                            <input type="text" name="items[<?php echo $index; ?>][title]" value="<?php echo site_escape(isset($item['title']) ? $item['title'] : ''); ?>" maxlength="30" required>
                        </label>
                    </div>
                    <div class="form-grid mt-14">
                        <label class="admin-field">
                            <div class="admin-field-head">
                                <span>URL o ruta del documento</span>
                                <span class="admin-field-limit">máx. 200 car.</span>
                            </div>
                            <input type="text" name="items[<?php echo $index; ?>][url]" value="<?php echo site_escape(isset($item['url']) ? $item['url'] : ''); ?>" maxlength="200" required>
                        </label>
                        <div class="admin-field">
                            <div class="admin-field-head">
                                <span>Destacado</span>
                            </div>
                            <label class="checkbox-field">
                                <input type="checkbox" name="items[<?php echo $index; ?>][is_featured]" value="1" <?php echo !empty($item['is_featured']) ? 'checked' : ''; ?>>
                                <span>Mostrar como tarjeta destacada (fondo dorado)</span>
                            </label>
                        </div>
                    </div>
                </div>
                <?php } ?>
            </div>
            <button type="button" class="btn-add" data-action="add-item" data-template="tmpl-normativa" data-target="items-container">+ Agregar otro documento</button>
        </section>

        <section class="editor-section">
            <h2 class="editor-section-title"><span>03</span> Nota al pie y contacto</h2>
            <?php admin_field('note_text', 'Texto aclaratorio', $content, 'textarea', 180); ?>
            <div class="form-grid">
                <?php admin_field('note_action_text', 'Texto del botón / enlace', $content, 'text', 30); ?>
                <?php admin_field('note_email', 'Correo de consulta', $content, 'email', 50); ?>
            </div>
        </section>

        <div class="editor-actions">
            <p>Al guardar se valida la información, se crea una copia de respaldo automática y se actualiza el archivo JSON.</p>
            <button type="submit">Guardar cambios <span>→</span></button>
        </div>
    </form>
</main>

<template id="tmpl-normativa">
    <div class="repeater-item" data-index="__INDEX__">
        <div class="repeater-head">
            <strong>Documento #<span class="item-number">__NUMBER__</span></strong>
            <button type="button" class="btn-remove">Eliminar</button>
        </div>
        <div class="form-grid">
            <label class="admin-field">
                <div class="admin-field-head">
                    <span>Categoría / Antetítulo</span>
                    <span class="admin-field-limit">máx. 35 car.</span>
                </div>
                <input type="text" name="items[__INDEX__][kicker]" maxlength="35" required>
            </label>
            <label class="admin-field">
                <div class="admin-field-head">
                    <span>Título visible</span>
                    <span class="admin-field-limit">máx. 30 car.</span>
                </div>
                <input type="text" name="items[__INDEX__][title]" maxlength="30" required>
            </label>
        </div>
        <div class="form-grid mt-14">
            <label class="admin-field">
                <div class="admin-field-head">
                    <span>URL o ruta del documento</span>
                    <span class="admin-field-limit">máx. 200 car.</span>
                </div>
                <input type="text" name="items[__INDEX__][url]" maxlength="200" required>
            </label>
            <div class="admin-field">
                <div class="admin-field-head">
                    <span>Destacado</span>
                </div>
                <label class="checkbox-field">
                    <input type="checkbox" name="items[__INDEX__][is_featured]" value="1">
                    <span>Mostrar como tarjeta destacada (fondo dorado)</span>
                </label>
            </div>
        </div>
    </div>
</template>

<?php admin_render_end(); ?>
