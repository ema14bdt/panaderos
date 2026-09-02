<?php

require_once 'bootstrap.php';
admin_require_login();

$content = site_content('servicios', site_servicios_defaults());
$message = '';
$error = '';

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        admin_verify_csrf();
        $content = admin_validate_servicios($_POST);
        admin_atomic_json('servicios.json', $content);
        admin_audit('update_servicios');
        $message = 'Los cambios en Beneficios y Servicios fueron guardados correctamente.';
    } catch (Throwable $exception) {
        $error = $exception->getMessage();
        if (isset($_POST['items']) && is_array($_POST['items'])) {
            $content['items'] = $_POST['items'];
        }
        foreach (array('page_label', 'page_title', 'page_intro', 'section_kicker', 'section_title', 'callout_kicker', 'callout_title', 'callout_action_text', 'callout_email') as $field) {
            if (isset($_POST[$field])) {
                $content[$field] = $_POST[$field];
            }
        }
    }
}

admin_render_start('Editar servicios y beneficios');
?>
<main class="admin-shell">
    <header class="admin-header">
        <a class="admin-mark" href="index.php">Panaderos <span>Administración</span></a>
        <a class="admin-logout" href="logout.php">Cerrar sesión</a>
    </header>
    <nav class="admin-breadcrumb"><a href="index.php">Panel</a><span>/</span> Beneficios y servicios</nav>
    <section class="editor-heading">
        <div>
            <p>Edición de contenido</p>
            <h1>Beneficios y <em>servicios.</em></h1>
        </div>
        <a href="../servicios.php" target="_blank" rel="noopener noreferrer">Ver página <span>↗</span></a>
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
            <h2 class="editor-section-title"><span>02</span> Listado de beneficios</h2>
            <div id="items-container" class="repeater-list" data-array-name="items">
                <?php foreach ($content['items'] as $index => $item) { ?>
                <div class="repeater-item" data-index="<?php echo $index; ?>">
                    <div class="repeater-head">
                        <strong>Beneficio #<span class="item-number"><?php echo $index + 1; ?></span></strong>
                        <button type="button" class="btn-remove">Eliminar</button>
                    </div>
                    <div class="form-grid--3">
                        <label class="admin-field">
                            <div class="admin-field-head">
                                <span>Título</span>
                                <span class="admin-field-limit">máx. 35 car.</span>
                            </div>
                            <input type="text" name="items[<?php echo $index; ?>][title]" value="<?php echo site_escape(isset($item['title']) ? $item['title'] : ''); ?>" maxlength="35" required>
                        </label>
                        <label class="admin-field">
                            <div class="admin-field-head">
                                <span>Icono FontAwesome</span>
                                <span class="admin-field-limit">máx. 30 car.</span>
                            </div>
                            <input type="text" name="items[<?php echo $index; ?>][icon]" value="<?php echo site_escape(isset($item['icon']) ? $item['icon'] : 'fa-check'); ?>" maxlength="30" required>
                        </label>
                        <label class="admin-field">
                            <div class="admin-field-head">
                                <span>Estilo de tarjeta</span>
                            </div>
                            <select name="items[<?php echo $index; ?>][theme]">
                                <option value="" <?php echo empty($item['theme']) ? 'selected' : ''; ?>>Predeterminado (Blanco)</option>
                                <option value="ink" <?php echo isset($item['theme']) && $item['theme'] === 'ink' ? 'selected' : ''; ?>>Oscuro (Ink)</option>
                                <option value="sun" <?php echo isset($item['theme']) && $item['theme'] === 'sun' ? 'selected' : ''; ?>>Dorado (Sun)</option>
                            </select>
                        </label>
                    </div>
                    <label class="admin-field mt-14">
                        <div class="admin-field-head">
                            <span>Descripción breve</span>
                            <span class="admin-field-limit">máx. 140 car.</span>
                        </div>
                        <input type="text" name="items[<?php echo $index; ?>][description]" value="<?php echo site_escape(isset($item['description']) ? $item['description'] : ''); ?>" maxlength="140" required>
                    </label>
                    <label class="admin-field mt-14">
                        <div class="admin-field-head">
                            <span>Detalles / Requisitos / Horarios</span>
                            <span class="admin-field-limit">máx. 140 car.</span>
                        </div>
                        <textarea name="items[<?php echo $index; ?>][detail]" rows="2" maxlength="140" required><?php echo site_escape(isset($item['detail']) ? $item['detail'] : ''); ?></textarea>
                    </label>
                </div>
                <?php } ?>
            </div>
            <button type="button" class="btn-add" data-action="add-item" data-template="tmpl-item" data-target="items-container">+ Agregar otro beneficio</button>
        </section>

        <section class="editor-section">
            <h2 class="editor-section-title"><span>03</span> Bloque de consultas y contacto</h2>
            <div class="form-grid">
                <?php admin_field('callout_kicker', 'Antetítulo de consulta', $content, 'text', 35); ?>
                <?php admin_field('callout_action_text', 'Texto del enlace', $content, 'text', 30); ?>
            </div>
            <?php admin_field('callout_title', 'Texto destacado', $content, 'text', 80); ?>
            <?php admin_field('callout_email', 'Correo para consultas', $content, 'email', 50); ?>
        </section>

        <div class="editor-actions">
            <p>Al guardar se valida la información, se crea una copia de respaldo automática y se actualiza el archivo JSON.</p>
            <button type="submit">Guardar cambios <span>→</span></button>
        </div>
    </form>
</main>

<template id="tmpl-item">
    <div class="repeater-item" data-index="__INDEX__">
        <div class="repeater-head">
            <strong>Beneficio #<span class="item-number">__NUMBER__</span></strong>
            <button type="button" class="btn-remove">Eliminar</button>
        </div>
        <div class="form-grid--3">
            <label class="admin-field">
                <div class="admin-field-head">
                    <span>Título</span>
                    <span class="admin-field-limit">máx. 35 car.</span>
                </div>
                <input type="text" name="items[__INDEX__][title]" maxlength="35" required>
            </label>
            <label class="admin-field">
                <div class="admin-field-head">
                    <span>Icono FontAwesome</span>
                    <span class="admin-field-limit">máx. 30 car.</span>
                </div>
                <input type="text" name="items[__INDEX__][icon]" value="fa-check" maxlength="30" required>
            </label>
            <label class="admin-field">
                <div class="admin-field-head">
                    <span>Estilo de tarjeta</span>
                </div>
                <select name="items[__INDEX__][theme]">
                    <option value="">Predeterminado (Blanco)</option>
                    <option value="ink">Oscuro (Ink)</option>
                    <option value="sun">Dorado (Sun)</option>
                </select>
            </label>
        </div>
        <label class="admin-field mt-14">
            <div class="admin-field-head">
                <span>Descripción breve</span>
                <span class="admin-field-limit">máx. 140 car.</span>
            </div>
            <input type="text" name="items[__INDEX__][description]" maxlength="140" required>
        </label>
        <label class="admin-field mt-14">
            <div class="admin-field-head">
                <span>Detalles / Requisitos / Horarios</span>
                <span class="admin-field-limit">máx. 140 car.</span>
            </div>
            <textarea name="items[__INDEX__][detail]" rows="2" maxlength="140" required></textarea>
        </label>
    </div>
</template>

<?php admin_render_end(); ?>
