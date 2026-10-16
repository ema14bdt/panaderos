<?php

require_once 'bootstrap.php';
admin_require_login();

$content = site_content('afiliaciones', site_afiliaciones_defaults());
$message = '';
$error = '';

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        admin_verify_csrf();
        $content = admin_validate_afiliaciones($_POST);
        admin_atomic_json('afiliaciones.json', $content);
        admin_audit('update_afiliaciones');
        $message = 'Los cambios en Afiliaciones fueron guardados correctamente.';
    } catch (Throwable $exception) {
        $error = $exception->getMessage();
        if (isset($_POST['requirements']) && is_array($_POST['requirements'])) {
            $content['requirements'] = $_POST['requirements'];
        }
        $fields = array(
            'page_label', 'page_title', 'page_intro',
            'section_kicker', 'section_title',
            'venue_kicker', 'venue_lead', 'venue_address', 'venue_maps_url',
            'requirements_kicker', 'requirements_title',
            'highlight_title', 'highlight_text'
        );
        foreach ($fields as $field) {
            if (isset($_POST[$field])) {
                $content[$field] = $_POST[$field];
            }
        }
    }
}

admin_render_start('Editar afiliaciones');
?>
<main class="admin-shell">
    <?php admin_header_html(); ?>
    <nav class="admin-breadcrumb"><a href="index.php">Panel</a><span>/</span> Afiliaciones</nav>
    <section class="editor-heading">
        <div>
            <p>Edición de contenido</p>
            <h1>Afiliaciones <em>SOEPLa.</em></h1>
        </div>
        <a href="../afiliaciones.php" target="_blank" rel="noopener noreferrer">Ver página <span>↗</span></a>
    </section>

    <?php if ($message !== '') { ?><p class="admin-success" role="status"><?php echo site_escape($message); ?></p><?php } ?>
    <?php if ($error !== '') { ?><p class="admin-error" role="alert"><?php echo site_escape($error); ?></p><?php } ?>

    <form class="editor-form" method="post" autocomplete="off">
        <input type="hidden" name="csrf_token" value="<?php echo site_escape(admin_csrf_token()); ?>">

        <section class="editor-section">
            <h2 class="editor-section-title"><span>01</span> Cabecera y presentación</h2>
            <div class="form-grid">
                <?php admin_field('page_label', 'Etiqueta superior', $content, 'text', 60); ?>
                <?php admin_field('page_title', 'Título de la página', $content, 'text', 50); ?>
            </div>
            <?php admin_field('page_intro', 'Texto introductorio', $content, 'textarea', 250); ?>
            <div class="form-grid">
                <?php admin_field('section_kicker', 'Antetítulo de sección', $content, 'text', 45); ?>
                <?php admin_field('section_title', 'Título principal de sección', $content, 'text', 70); ?>
            </div>
        </section>

        <section class="editor-section">
            <h2 class="editor-section-title"><span>02</span> Sede Sindical y atención presencial</h2>
            <div class="form-grid">
                <?php admin_field('venue_kicker', 'Etiqueta de la sede', $content, 'text', 45); ?>
                <?php admin_field('venue_lead', 'Texto de invitación', $content, 'text', 90); ?>
            </div>
            <div class="form-grid">
                <?php admin_field('venue_address', 'Dirección de la sede', $content, 'text', 140); ?>
                <?php admin_field('venue_maps_url', 'Enlace Google Maps', $content, 'url', 350); ?>
            </div>
        </section>

        <section class="editor-section">
            <h2 class="editor-section-title"><span>03</span> Requisitos para afiliarte</h2>
            <div class="form-grid">
                <?php admin_field('requirements_kicker', 'Antetítulo de requisitos', $content, 'text', 45); ?>
                <?php admin_field('requirements_title', 'Título de requisitos', $content, 'text', 70); ?>
            </div>

            <div id="requirements-container" class="repeater-list" data-array-name="requirements">
                <?php foreach ($content['requirements'] as $index => $req) { ?>
                <div class="repeater-item" data-index="<?php echo $index; ?>">
                    <div class="repeater-head">
                        <strong>Requisito #<span class="item-number"><?php echo $index + 1; ?></span>: <span class="item-title-preview"><?php echo site_escape(isset($req['title']) ? $req['title'] : ''); ?></span></strong>
                        <button type="button" class="btn-remove">Eliminar</button>
                    </div>
                    <div class="form-grid">
                        <label class="admin-field">
                            <div class="admin-field-head">
                                <span>Número / Orden</span>
                                <span class="admin-field-limit">máx. 10 car.</span>
                            </div>
                            <input type="text" name="requirements[<?php echo $index; ?>][number]" value="<?php echo site_escape(isset($req['number']) ? $req['number'] : sprintf('%02d', $index + 1)); ?>" maxlength="10" required>
                        </label>
                        <label class="admin-field">
                            <div class="admin-field-head">
                                <span>Título del requisito</span>
                                <span class="admin-field-limit">máx. 90 car.</span>
                            </div>
                            <input type="text" name="requirements[<?php echo $index; ?>][title]" value="<?php echo site_escape(isset($req['title']) ? $req['title'] : ''); ?>" maxlength="90" required>
                        </label>
                    </div>
                    <div class="form-grid mt-14">
                        <label class="admin-field">
                            <div class="admin-field-head">
                                <span>Detalle / Descripción</span>
                                <span class="admin-field-limit">máx. 200 car.</span>
                            </div>
                            <input type="text" name="requirements[<?php echo $index; ?>][description]" value="<?php echo site_escape(isset($req['description']) ? $req['description'] : ''); ?>" maxlength="200" required>
                        </label>
                        <label class="admin-field">
                            <div class="admin-field-head">
                                <span>Etiqueta / Badge</span>
                                <span class="admin-field-limit">máx. 35 car.</span>
                            </div>
                            <input type="text" name="requirements[<?php echo $index; ?>][badge]" value="<?php echo site_escape(isset($req['badge']) ? $req['badge'] : ''); ?>" maxlength="35" required>
                        </label>
                    </div>
                    <div class="mt-14">
                        <?php admin_render_icon_picker("requirements[{$index}][icon]", isset($req['icon']) ? $req['icon'] : 'fa-check'); ?>
                    </div>
                </div>
                <?php } ?>
            </div>
            <button type="button" class="btn-add" data-action="add-item" data-template="tmpl-requirement" data-target="requirements-container">+ Agregar otro requisito</button>
        </section>

        <section class="editor-section">
            <h2 class="editor-section-title"><span>04</span> Mensaje de cierre</h2>
            <div class="form-grid">
                <?php admin_field('highlight_title', 'Título destacado', $content, 'text', 90); ?>
            </div>
            <?php admin_field('highlight_text', 'Texto de cierre / Aclaración', $content, 'textarea', 250); ?>
        </section>

        <div class="editor-actions">
            <p>Al guardar se valida la información, se crea una copia de respaldo automática y se actualiza el archivo JSON.</p>
            <button type="submit">Guardar cambios <span>→</span></button>
        </div>
    </form>
</main>

<template id="tmpl-requirement">
    <div class="repeater-item" data-index="__INDEX__">
        <div class="repeater-head">
            <strong>Requisito #<span class="item-number">__NUMBER__</span></strong>
            <button type="button" class="btn-remove">Eliminar</button>
        </div>
        <div class="form-grid">
            <label class="admin-field">
                <div class="admin-field-head">
                    <span>Número / Orden</span>
                    <span class="admin-field-limit">máx. 10 car.</span>
                </div>
                <input type="text" name="requirements[__INDEX__][number]" value="0__NUMBER__" maxlength="10" required>
            </label>
            <label class="admin-field">
                <div class="admin-field-head">
                    <span>Título del requisito</span>
                    <span class="admin-field-limit">máx. 90 car.</span>
                </div>
                <input type="text" name="requirements[__INDEX__][title]" value="" maxlength="90" required>
            </label>
        </div>
        <div class="form-grid mt-14">
            <label class="admin-field">
                <div class="admin-field-head">
                    <span>Detalle / Descripción</span>
                    <span class="admin-field-limit">máx. 200 car.</span>
                </div>
                <input type="text" name="requirements[__INDEX__][description]" value="" maxlength="200" required>
            </label>
            <label class="admin-field">
                <div class="admin-field-head">
                    <span>Etiqueta / Badge</span>
                    <span class="admin-field-limit">máx. 35 car.</span>
                </div>
                <input type="text" name="requirements[__INDEX__][badge]" value="Requisito" maxlength="35" required>
            </label>
        </div>
        <div class="mt-14">
            <?php admin_render_icon_picker('requirements[__INDEX__][icon]', 'fa-check'); ?>
        </div>
    </div>
</template>
<?php admin_render_end(); ?>
