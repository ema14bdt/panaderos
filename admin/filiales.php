<?php

require_once 'bootstrap.php';
admin_require_login();

$content = site_content('filiales', site_filiales_defaults());
$message = '';
$error = '';

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        admin_verify_csrf();
        $content = admin_validate_filiales($_POST);
        admin_atomic_json('filiales.json', $content);
        admin_audit('update_filiales');
        $message = 'Los cambios en el Directorio de Filiales fueron guardados correctamente.';
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

admin_render_start('Editar directorio de filiales');
?>
<main class="admin-shell">
    <?php admin_header_html(); ?>
    <nav class="admin-breadcrumb"><a href="index.php">Panel</a><span>/</span> Directorio de filiales</nav>
    <section class="editor-heading">
        <div>
            <p>Edición de contenido</p>
            <h1>Directorio de <em>filiales.</em></h1>
        </div>
        <a href="../filiales.php" target="_blank" rel="noopener noreferrer">Ver página <span>↗</span></a>
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
            <h2 class="editor-section-title"><span>02</span> Listado de filiales</h2>
            <div id="items-container" class="repeater-list" data-array-name="items">
                <?php foreach ($content['items'] as $index => $item) { ?>
                <div class="repeater-item" data-index="<?php echo $index; ?>">
                    <div class="repeater-head">
                        <strong>Filial #<span class="item-number"><?php echo $index + 1; ?></span>: <span class="item-title-preview"><?php echo site_escape(isset($item['name']) ? $item['name'] : ''); ?></span></strong>
                        <button type="button" class="btn-remove">Eliminar</button>
                    </div>
                    <div class="form-grid">
                        <label class="admin-field">
                            <div class="admin-field-head">
                                <span>Nombre de la filial</span>
                                <span class="admin-field-limit">máx. 35 car.</span>
                            </div>
                            <input type="text" name="items[<?php echo $index; ?>][name]" value="<?php echo site_escape(isset($item['name']) ? $item['name'] : ''); ?>" maxlength="35" required>
                        </label>
                        <label class="admin-field">
                            <div class="admin-field-head">
                                <span>Dirección y código postal</span>
                                <span class="admin-field-limit">máx. 50 car.</span>
                            </div>
                            <input type="text" name="items[<?php echo $index; ?>][address]" value="<?php echo site_escape(isset($item['address']) ? $item['address'] : ''); ?>" maxlength="50" required>
                        </label>
                    </div>
                    <div class="form-grid mt-14">
                        <label class="admin-field">
                            <div class="admin-field-head">
                                <span>Teléfono(s) de contacto</span>
                                <span class="admin-field-limit">máx. 35 car.</span>
                            </div>
                            <input type="text" name="items[<?php echo $index; ?>][phone]" value="<?php echo site_escape(isset($item['phone']) ? $item['phone'] : ''); ?>" maxlength="35" required>
                        </label>
                        <label class="admin-field">
                            <div class="admin-field-head">
                                <span>Secretaría general / Responsable</span>
                                <span class="admin-field-limit">máx. 35 car.</span>
                            </div>
                            <input type="text" name="items[<?php echo $index; ?>][secretary]" value="<?php echo site_escape(isset($item['secretary']) ? $item['secretary'] : ''); ?>" maxlength="35" required>
                        </label>
                    </div>
                </div>
                <?php } ?>
            </div>
            <button type="button" class="btn-add" data-action="add-item" data-template="tmpl-filial" data-target="items-container">+ Agregar otra filial</button>
        </section>

        <div class="editor-actions">
            <p>Al guardar se valida la información, se crea una copia de respaldo automática y se actualiza el archivo JSON.</p>
            <button type="submit">Guardar cambios <span>→</span></button>
        </div>
    </form>
</main>

<template id="tmpl-filial">
    <div class="repeater-item" data-index="__INDEX__">
        <div class="repeater-head">
            <strong>Filial #<span class="item-number">__NUMBER__</span></strong>
            <button type="button" class="btn-remove">Eliminar</button>
        </div>
        <div class="form-grid">
            <label class="admin-field">
                <div class="admin-field-head">
                    <span>Nombre de la filial</span>
                    <span class="admin-field-limit">máx. 35 car.</span>
                </div>
                <input type="text" name="items[__INDEX__][name]" maxlength="35" required>
            </label>
            <label class="admin-field">
                <div class="admin-field-head">
                    <span>Dirección y código postal</span>
                    <span class="admin-field-limit">máx. 50 car.</span>
                </div>
                <input type="text" name="items[__INDEX__][address]" maxlength="50" required>
            </label>
        </div>
        <div class="form-grid mt-14">
            <label class="admin-field">
                <div class="admin-field-head">
                    <span>Teléfono(s) de contacto</span>
                    <span class="admin-field-limit">máx. 35 car.</span>
                </div>
                <input type="text" name="items[__INDEX__][phone]" maxlength="35" required>
            </label>
            <label class="admin-field">
                <div class="admin-field-head">
                    <span>Secretaría general / Responsable</span>
                    <span class="admin-field-limit">máx. 35 car.</span>
                </div>
                <input type="text" name="items[__INDEX__][secretary]" maxlength="35" required>
            </label>
        </div>
    </div>
</template>

<?php admin_render_end(); ?>
