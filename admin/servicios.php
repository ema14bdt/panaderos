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

function admin_servicios_icon_catalog()
{
    return array(
        'Salud y Farmacia' => array(
            'fa-plus-square' => 'Farmacia / Medicamentos',
            'fa-flask'       => 'Laboratorio / Análisis',
            'fa-user-md'     => 'Médico / Consulta general',
            'fa-stethoscope' => 'Atención médica / Chequeos',
            'fa-hospital-o'  => 'Hospital / Sanatorio',
            'fa-medkit'      => 'Botiquín / Auxilios',
            'fa-ambulance'   => 'Emergencias / Ambulancia',
            'fa-eye'         => 'Óptica / Oftalmología'
        ),
        'Familia y Hogar' => array(
            'fa-heart'   => 'Casamiento / Unión',
            'fa-users'   => 'Familia / Nacimiento / Afiliados',
            'fa-smile-o' => 'Niñez / Infancia',
            'fa-home'    => 'Hogar / Vivienda'
        ),
        'Servicios y Traslados' => array(
            'fa-truck' => 'Mudanzas / Fletes',
            'fa-car'   => 'Remise / Movilidad',
            'fa-plane' => 'Viajes / Turismo'
        ),
        'Educación y Capacitación' => array(
            'fa-book'   => 'Kits escolares / Libros',
            'fa-pencil' => 'Útiles / Capacitación'
        ),
        'Gremial y Legal' => array(
            'fa-legal'       => 'Asesoría legal / Abogado',
            'fa-briefcase'   => 'Trámites gremiales',
            'fa-money'       => 'Subsidios / Ayuda económica',
            'fa-credit-card' => 'Credencial / Tarjeta'
        ),
        'Turismo y Recreación' => array(
            'fa-sun-o'    => 'Verano / Temporada',
            'fa-umbrella' => 'Vacaciones / Turismo',
            'fa-leaf'     => 'Camping / Aire libre',
            'fa-coffee'   => 'Refrigerio / Encuentros'
        ),
        'Beneficios Generales' => array(
            'fa-gift'         => 'Presentes / Cajas',
            'fa-tag'          => 'Descuentos en comercios',
            'fa-ticket'       => 'Entradas y eventos',
            'fa-shield'       => 'Seguro / Cobertura',
            'fa-check-circle' => 'Beneficio general',
            'fa-calendar'     => 'Turnos y reservas'
        )
    );
}

function admin_servicios_icon_name($iconCode)
{
    $catalog = admin_servicios_icon_catalog();
    foreach ($catalog as $cat => $icons) {
        if (isset($icons[$iconCode])) {
            return $icons[$iconCode];
        }
    }
    return 'Icono seleccionado';
}

function admin_render_icon_picker($inputName, $selectedIcon)
{
    $catalog = admin_servicios_icon_catalog();
    $iconName = admin_servicios_icon_name($selectedIcon);
    ?>
    <div class="icon-picker-field">
        <div class="admin-field-head">
            <span>Icono representativo</span>
            <span class="admin-field-limit">Seleccioná visualmente</span>
        </div>
        <input type="hidden" name="<?php echo $inputName; ?>" class="icon-picker-input" value="<?php echo site_escape($selectedIcon); ?>" required>
        
        <div class="icon-picker-display">
            <div class="icon-picker-current">
                <div class="icon-preview-box">
                    <i class="fa <?php echo site_escape($selectedIcon); ?>" aria-hidden="true"></i>
                </div>
                <div class="icon-preview-meta">
                    <strong class="icon-preview-name"><?php echo site_escape($iconName); ?></strong>
                    <span class="icon-preview-code"><?php echo site_escape($selectedIcon); ?></span>
                </div>
            </div>
            <button type="button" class="btn-toggle-icon-palette" title="Cambiar icono visual"><i class="fa fa-th"></i> Elegir icono...</button>
        </div>

        <div class="icon-picker-palette" style="display:none;">
            <div class="icon-palette-head">
                <span>Elegí el icono haciendo clic sobre él:</span>
                <button type="button" class="btn-close-palette" title="Cerrar">✕</button>
            </div>
            <div class="icon-palette-groups">
                <?php foreach ($catalog as $groupName => $groupIcons) { ?>
                <div class="icon-palette-group">
                    <span class="icon-palette-group-title"><?php echo site_escape($groupName); ?></span>
                    <div class="icon-palette-grid">
                        <?php foreach ($groupIcons as $faClass => $faLabel) { 
                            $isSelected = ($faClass === $selectedIcon);
                        ?>
                        <button type="button" class="icon-option<?php echo $isSelected ? ' is-selected' : ''; ?>" data-icon="<?php echo site_escape($faClass); ?>" data-name="<?php echo site_escape($faLabel); ?>" title="<?php echo site_escape($faLabel); ?>">
                            <i class="fa <?php echo site_escape($faClass); ?>" aria-hidden="true"></i>
                            <span class="icon-option-label"><?php echo site_escape($faLabel); ?></span>
                        </button>
                        <?php } ?>
                    </div>
                </div>
                <?php } ?>
            </div>
        </div>
    </div>
    <?php
}

function admin_render_benefit_image_picker($inputName, $imagePath)
{
    $hasImage = !empty($imagePath);
    ?>
    <div class="image-picker-field mt-14" data-folder="servicios">
        <div class="admin-field-head">
            <span>Foto o imagen ilustrativa (opcional)</span>
            <span class="admin-field-limit">máx. 200 car.</span>
        </div>
        <div class="image-picker-wrap">
            <div class="image-preview-thumb image-preview-thumb--rect">
                <img src="../<?php echo site_escape($imagePath); ?>" alt="Previsualización" style="<?php echo $hasImage ? '' : 'display:none;'; ?>">
                <div class="image-preview-empty" style="<?php echo $hasImage ? 'display:none;' : ''; ?>">
                    <i class="fa fa-picture-o" aria-hidden="true"></i>
                    <span>Sin foto</span>
                </div>
            </div>
            <div class="image-picker-body">
                <div class="image-guideline">
                    <span><strong>Formato recomendado:</strong> 4:3 o 16:9 Horizontal (800 × 500 px) · JPG, PNG o WebP hasta 2 MB. Dejá vacío si este beneficio no lleva foto.</span>
                </div>
                <div class="image-picker-actions">
                    <button type="button" class="image-upload-btn"><i class="fa fa-image"></i> Subir foto...</button>
                    <button type="button" class="btn-clear-image" title="Quitar imagen seleccionada"><i class="fa fa-trash-o"></i> Quitar foto</button>
                    <input type="file" class="image-file-input" accept="image/jpeg,image/png,image/webp" style="display:none;">
                    <input type="text" class="image-path-input" name="<?php echo $inputName; ?>" value="<?php echo site_escape($imagePath); ?>" maxlength="200" placeholder="images/servicios/ejemplo.jpg">
                </div>
                <span class="image-status-badge"></span>
            </div>
        </div>
    </div>
    <?php
}

admin_render_start('Editar servicios y beneficios');
?>
<main class="admin-shell">
    <?php admin_header_html(); ?>
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
                <?php foreach ($content['items'] as $index => $item) { 
                    $itemIcon = isset($item['icon']) && !empty($item['icon']) ? $item['icon'] : 'fa-check-circle';
                    $itemImage = isset($item['image']) ? $item['image'] : '';
                    $itemAddress = isset($item['address']) ? $item['address'] : '';
                ?>
                <div class="repeater-item" data-index="<?php echo $index; ?>">
                    <div class="repeater-head">
                        <strong>Beneficio #<span class="item-number"><?php echo $index + 1; ?></span>: <span class="item-title-preview"><?php echo site_escape(isset($item['title']) ? $item['title'] : ''); ?></span></strong>
                        <button type="button" class="btn-remove">Eliminar</button>
                    </div>

                    <div class="form-grid">
                        <label class="admin-field">
                            <div class="admin-field-head">
                                <span>Título</span>
                                <span class="admin-field-limit">máx. 35 car.</span>
                            </div>
                            <input type="text" name="items[<?php echo $index; ?>][title]" value="<?php echo site_escape(isset($item['title']) ? $item['title'] : ''); ?>" maxlength="35" required>
                        </label>
                        <label class="admin-field">
                            <div class="admin-field-head">
                                <span>Estilo de tarjeta</span>
                            </div>
                            <select name="items[<?php echo $index; ?>][theme]">
                                <option value="" <?php echo empty($item['theme']) ? 'selected' : ''; ?>>Predeterminado (Blanco / Arena)</option>
                                <option value="ink" <?php echo isset($item['theme']) && $item['theme'] === 'ink' ? 'selected' : ''; ?>>Oscuro (Ink)</option>
                                <option value="sun" <?php echo isset($item['theme']) && $item['theme'] === 'sun' ? 'selected' : ''; ?>>Dorado (Sun)</option>
                            </select>
                        </label>
                    </div>

                    <div class="mt-14">
                        <?php admin_render_icon_picker("items[{$index}][icon]", $itemIcon); ?>
                    </div>

                    <?php admin_render_benefit_image_picker("items[{$index}][image]", $itemImage); ?>

                    <label class="admin-field mt-14">
                        <div class="admin-field-head">
                            <span>Dirección física (opcional - abre en Google Maps / app móvil)</span>
                            <span class="admin-field-limit">máx. 70 car.</span>
                        </div>
                        <input type="text" name="items[<?php echo $index; ?>][address]" value="<?php echo site_escape($itemAddress); ?>" maxlength="70" placeholder="Ej: Ituzaingó 1807, Lanús Este">
                    </label>

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
            <strong>Beneficio #<span class="item-number">__NUMBER__</span>: <span class="item-title-preview">Nuevo</span></strong>
            <button type="button" class="btn-remove">Eliminar</button>
        </div>
        <div class="form-grid">
            <label class="admin-field">
                <div class="admin-field-head">
                    <span>Título</span>
                    <span class="admin-field-limit">máx. 35 car.</span>
                </div>
                <input type="text" name="items[__INDEX__][title]" maxlength="35" required>
            </label>
            <label class="admin-field">
                <div class="admin-field-head">
                    <span>Estilo de tarjeta</span>
                </div>
                <select name="items[__INDEX__][theme]">
                    <option value="">Predeterminado (Blanco / Arena)</option>
                    <option value="ink">Oscuro (Ink)</option>
                    <option value="sun">Dorado (Sun)</option>
                </select>
            </label>
        </div>

        <div class="mt-14">
            <?php admin_render_icon_picker('items[__INDEX__][icon]', 'fa-check-circle'); ?>
        </div>

        <?php admin_render_benefit_image_picker('items[__INDEX__][image]', ''); ?>

        <label class="admin-field mt-14">
            <div class="admin-field-head">
                <span>Dirección física (opcional - abre en Google Maps / app móvil)</span>
                <span class="admin-field-limit">máx. 70 car.</span>
            </div>
            <input type="text" name="items[__INDEX__][address]" maxlength="70" placeholder="Ej: Ituzaingó 1807, Lanús Este">
        </label>

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
