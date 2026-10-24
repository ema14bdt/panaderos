<?php
require_once __DIR__ . '/site-content.php';
$afiliaciones_content = site_content('afiliaciones', site_afiliaciones_defaults());
$home_content = site_content('home', site_home_defaults());

$page_title = $afiliaciones_content['page_title'];
$page_label = $afiliaciones_content['page_label'];
$page_intro = $afiliaciones_content['page_intro'];
require_once('page-header.php');
?>
<section class="page-content affiliation-page">
    <div class="container">
        <p class="section-kicker"><?php echo site_escape($afiliaciones_content['section_kicker']); ?></p>
        <h2 class="section-title"><?php echo site_escape($afiliaciones_content['section_title']); ?></h2>

        <div class="affiliation-venue">
            <div class="affiliation-venue__icon-box" aria-hidden="true">
                <i class="fa fa-map-marker"></i>
            </div>
            <div class="affiliation-venue__body">
                <span class="affiliation-venue__eyebrow"><?php echo site_escape($afiliaciones_content['venue_kicker']); ?></span>
                <h3 class="affiliation-venue__headline"><?php echo site_escape($afiliaciones_content['venue_lead']); ?></h3>
                <p class="affiliation-venue__address">
                    <span class="affiliation-venue__pin" aria-hidden="true">📍</span> <?php echo site_escape($afiliaciones_content['venue_address']); ?>
                </p>
            </div>
            <div class="affiliation-venue__actions">
                <a class="button button--primary" href="<?php echo site_escape($afiliaciones_content['venue_maps_url']); ?>" target="_blank" rel="noopener noreferrer">
                    Ver en mapa <i class="fa fa-external-link" aria-hidden="true"></i>
                </a>
                <a class="button button--secondary" href="tel:<?php echo site_escape(preg_replace('/[^0-9+]/', '', $home_content['phone'])); ?>">
                    <i class="fa fa-phone" aria-hidden="true"></i> <?php echo site_escape($home_content['phone_label']); ?>
                </a>
            </div>
        </div>

        <div class="affiliation-section">
            <div class="affiliation-section__header">
                <p class="section-kicker"><?php echo site_escape($afiliaciones_content['requirements_kicker']); ?></p>
                <h3 class="affiliation-section__title"><?php echo site_escape($afiliaciones_content['requirements_title']); ?></h3>
            </div>
            <div class="affiliation-grid">
                <?php foreach ($afiliaciones_content['requirements'] as $req) { ?>
                <div class="affiliation-card">
                    <div class="affiliation-card__top">
                        <span class="affiliation-card__num"><?php echo site_escape($req['number']); ?></span>
                        <span class="affiliation-card__icon" aria-hidden="true"><i class="fa <?php echo site_escape($req['icon']); ?>"></i></span>
                    </div>
                    <h4 class="affiliation-card__title"><?php echo site_escape($req['title']); ?></h4>
                    <p class="affiliation-card__desc"><?php echo site_escape($req['description']); ?></p>
                    <div class="affiliation-card__footer">
                        <span class="affiliation-badge"><?php echo site_escape($req['badge']); ?></span>
                    </div>
                </div>
                <?php } ?>
            </div>
        </div>

        <div class="affiliation-callout">
            <div class="affiliation-callout__copy">
                <span class="affiliation-callout__pill"><i class="fa fa-check" aria-hidden="true"></i> Trámite ágil</span>
                <h3 class="affiliation-callout__title"><?php echo site_escape($afiliaciones_content['highlight_title']); ?></h3>
                <p class="affiliation-callout__desc"><?php echo site_escape($afiliaciones_content['highlight_text']); ?></p>
            </div>
            <div class="affiliation-callout__actions">
                <a class="button button--primary" href="servicios.php">Conocer beneficios <i class="fa fa-arrow-right" aria-hidden="true"></i></a>
                <a class="button button--secondary" href="comision-directiva.php">El Sindicato <i class="fa fa-arrow-right" aria-hidden="true"></i></a>
            </div>
        </div>
    </div>
</section>
<?php require_once('footer.php'); ?>
<script src="js/jquery.js"></script><script src="js/bootstrap.min.js"></script><script src="js/main.js"></script>
</body></html>
