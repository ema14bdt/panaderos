<?php

require_once 'bootstrap.php';
admin_require_login();

$content = site_content('comision', site_comision_defaults());
$message = '';
$error = '';

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        admin_verify_csrf();
        $content = admin_validate_comision($_POST);
        admin_atomic_json('comision.json', $content);
        admin_audit('update_comision');
        $message = 'Los cambios en Comisión Directiva fueron guardados correctamente.';
    } catch (Throwable $exception) {
        $error = $exception->getMessage();
        if (isset($_POST['members']) && is_array($_POST['members'])) {
            $content['members'] = $_POST['members'];
        }
        foreach (array('page_label', 'page_title', 'page_intro', 'section_kicker', 'section_title') as $field) {
            if (isset($_POST[$field])) {
                $content[$field] = $_POST[$field];
            }
        }
    }
}

admin_render_start('Editar comisión directiva');
?>
<main class="admin-shell">
    <header class="admin-header">
        <a class="admin-mark" href="index.php">Panaderos <span>Administración</span></a>
        <a class="admin-logout" href="logout.php">Cerrar sesión</a>
    </header>
    <nav class="admin-breadcrumb"><a href="index.php">Panel</a><span>/</span> Comisión directiva</nav>
    <section class="editor-heading">
        <div>
            <p>Edición de contenido</p>
            <h1>Comisión <em>directiva.</em></h1>
        </div>
        <a href="../comision-directiva.php" target="_blank" rel="noopener noreferrer">Ver página <span>↗</span></a>
    </section>

    <?php if ($message !== '') { ?><p class="admin-success" role="status"><?php echo site_escape($message); ?></p><?php } ?>
    <?php if ($error !== '') { ?><p class="admin-error" role="alert"><?php echo site_escape($error); ?></p><?php } ?>

    <form class="editor-form" method="post" autocomplete="off">
        <input type="hidden" name="csrf_token" value="<?php echo site_escape(admin_csrf_token()); ?>">

        <section class="editor-section">
            <h2 class="editor-section-title"><span>01</span> Cabecera y presentación</h2>
            <div class="form-grid">
                <?php admin_field('page_label', 'Etiqueta superior', $content, 'text', 100); ?>
                <?php admin_field('page_title', 'Título de la página', $content, 'text', 100); ?>
            </div>
            <?php admin_field('page_intro', 'Texto introductorio', $content, 'textarea', 400); ?>
            <div class="form-grid">
                <?php admin_field('section_kicker', 'Antetítulo de sección', $content, 'text', 80); ?>
                <?php admin_field('section_title', 'Título de sección', $content, 'text', 120); ?>
            </div>
        </section>

        <section class="editor-section">
            <h2 class="editor-section-title"><span>02</span> Integrantes de la conducción</h2>
            <div id="members-container" class="repeater-list" data-array-name="members">
                <?php foreach ($content['members'] as $index => $member) { ?>
                <div class="repeater-item" data-index="<?php echo $index; ?>">
                    <div class="repeater-head">
                        <strong>Integrante #<span class="item-number"><?php echo $index + 1; ?></span>: <span class="item-title-preview"><?php echo site_escape(isset($member['name']) ? $member['name'] : ''); ?></span></strong>
                        <button type="button" class="btn-remove">Eliminar</button>
                    </div>
                    <div class="form-grid--3">
                        <label class="admin-field">
                            <div class="admin-field-head">
                                <span>Nombre y apellido</span>
                                <span class="admin-field-limit">máx. 100</span>
                            </div>
                            <input type="text" name="members[<?php echo $index; ?>][name]" value="<?php echo site_escape(isset($member['name']) ? $member['name'] : ''); ?>" maxlength="100" required>
                        </label>
                        <label class="admin-field">
                            <div class="admin-field-head">
                                <span>Cargo / Secretaría</span>
                                <span class="admin-field-limit">máx. 100</span>
                            </div>
                            <input type="text" name="members[<?php echo $index; ?>][role]" value="<?php echo site_escape(isset($member['role']) ? $member['role'] : ''); ?>" maxlength="100" required>
                        </label>
                        <label class="admin-field">
                            <div class="admin-field-head">
                                <span>Ruta de la fotografía</span>
                                <span class="admin-field-limit">máx. 300</span>
                            </div>
                            <input type="text" name="members[<?php echo $index; ?>][photo]" value="<?php echo site_escape(isset($member['photo']) ? $member['photo'] : 'images/directivos/sin-foto.jpg'); ?>" maxlength="300" required>
                        </label>
                    </div>
                </div>
                <?php } ?>
            </div>
            <button type="button" class="btn-add" data-action="add-item" data-template="tmpl-member" data-target="members-container">+ Agregar otro integrante</button>
        </section>

        <div class="editor-actions">
            <p>Al guardar se valida la información, se crea una copia de respaldo automática y se actualiza el archivo JSON.</p>
            <button type="submit">Guardar cambios <span>→</span></button>
        </div>
    </form>
</main>

<template id="tmpl-member">
    <div class="repeater-item" data-index="__INDEX__">
        <div class="repeater-head">
            <strong>Integrante #<span class="item-number">__NUMBER__</span></strong>
            <button type="button" class="btn-remove">Eliminar</button>
        </div>
        <div class="form-grid--3">
            <label class="admin-field">
                <div class="admin-field-head">
                    <span>Nombre y apellido</span>
                    <span class="admin-field-limit">máx. 100</span>
                </div>
                <input type="text" name="members[__INDEX__][name]" maxlength="100" required>
            </label>
            <label class="admin-field">
                <div class="admin-field-head">
                    <span>Cargo / Secretaría</span>
                    <span class="admin-field-limit">máx. 100</span>
                </div>
                <input type="text" name="members[__INDEX__][role]" maxlength="100" required>
            </label>
            <label class="admin-field">
                <div class="admin-field-head">
                    <span>Ruta de la fotografía</span>
                    <span class="admin-field-limit">máx. 300</span>
                </div>
                <input type="text" name="members[__INDEX__][photo]" value="images/directivos/sin-foto.jpg" maxlength="300" required>
            </label>
        </div>
    </div>
</template>

<?php admin_render_end(); ?>
