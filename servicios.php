<?php
require_once __DIR__ . '/site-content.php';
$servicios_content = site_content('servicios', site_servicios_defaults());

$page_title = $servicios_content['page_title'];
$page_label = $servicios_content['page_label'];
$page_intro = $servicios_content['page_intro'];
require_once('page-header.php');
?>
<section class="page-content">
    <div class="container">
        <p class="section-kicker"><?php echo site_escape($servicios_content['section_kicker']); ?></p>
        <h2 class="section-title"><?php echo site_escape($servicios_content['section_title']); ?></h2>
        <div class="content-grid benefits-grid">
            <?php foreach ($servicios_content['items'] as $item) {
                $hasImage = !empty($item['image']);
                $cardClass = 'content-card benefit-card' 
                    . ($hasImage ? ' benefit-card--with-media' : '') 
                    . (!empty($item['theme']) ? ' content-card--' . site_escape($item['theme']) : '');
                $iconClass = !empty($item['icon']) ? site_escape($item['icon']) : 'fa-check';
                $address = !empty($item['address']) ? trim($item['address']) : '';
            ?>
            <article class="<?php echo $cardClass; ?>">
                <?php if ($hasImage) { ?>
                <div class="benefit-card__media">
                    <img src="<?php echo site_escape($item['image']); ?>" alt="<?php echo site_escape($item['title']); ?>" loading="lazy">
                    <span class="benefit-card__icon-badge" aria-hidden="true">
                        <i class="fa <?php echo $iconClass; ?>"></i>
                    </span>
                </div>
                <?php } else { ?>
                <div class="benefit-card__header">
                    <span class="benefit-card__icon-wrap" aria-hidden="true">
                        <i class="fa <?php echo $iconClass; ?>"></i>
                    </span>
                </div>
                <?php } ?>

                <div class="benefit-card__content">
                    <h3 class="benefit-card__title"><?php echo site_escape($item['title']); ?></h3>
                    <p class="benefit-card__desc"><?php echo site_escape($item['description']); ?></p>

                    <?php if ($address !== '') { 
                        $mapsUrl = 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode($address);
                    ?>
                    <div class="benefit-card__location">
                        <a href="<?php echo site_escape($mapsUrl); ?>" target="_blank" rel="noopener noreferrer" class="benefit-location-btn" title="Abrir <?php echo site_escape($address); ?> en Google Maps">
                            <i class="fa fa-map-marker" aria-hidden="true"></i>
                            <span class="benefit-location-text"><?php echo site_escape($address); ?></span>
                            <span class="benefit-location-action">Ver mapa <i class="fa fa-external-link" aria-hidden="true"></i></span>
                        </a>
                    </div>
                    <?php } ?>

                    <?php if (!empty($item['detail'])) { ?>
                    <div class="benefit-card__footer">
                        <span class="benefit-detail"><?php echo nl2br(site_escape($item['detail'])); ?></span>
                    </div>
                    <?php } ?>
                </div>
            </article>
            <?php } ?>

            <aside class="benefits-callout">
                <div>
                    <p class="section-kicker"><?php echo site_escape($servicios_content['callout_kicker']); ?></p>
                    <h3><?php echo site_escape($servicios_content['callout_title']); ?></h3>
                </div>
                <a class="text-link" href="mailto:<?php echo site_escape($servicios_content['callout_email']); ?>"><?php echo site_escape($servicios_content['callout_action_text']); ?> <i class="fa fa-arrow-right" aria-hidden="true"></i></a>
            </aside>
        </div>
    </div>
</section>
<?php require_once('footer.php'); ?>
<script src="js/jquery.js"></script><script src="js/bootstrap.min.js"></script><script src="js/main.js"></script>
</body></html>
