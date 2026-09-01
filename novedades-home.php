<?php
require_once __DIR__ . '/site-content.php';
$novedades_content = site_content('novedades', site_novedades_defaults());
$home_content = site_content('home', site_home_defaults());
?>
<section class="news-now">
    <div>
        <p class="section-kicker"><?php echo site_escape($novedades_content['social_kicker']); ?></p>
        <h2><?php echo site_escape($novedades_content['social_title']); ?></h2>
        <p><?php echo site_escape($novedades_content['social_intro']); ?></p>
    </div>
    <div class="social-actions">
        <a class="button button--primary" href="<?php echo site_escape($home_content['instagram_url']); ?>" target="_blank" rel="noopener noreferrer"><i class="fa fa-instagram" aria-hidden="true"></i> Instagram</a>
        <a class="button button--primary" href="<?php echo site_escape($home_content['facebook_url']); ?>" target="_blank" rel="noopener noreferrer"><i class="fa fa-facebook" aria-hidden="true"></i> Facebook</a>
    </div>
</section>
