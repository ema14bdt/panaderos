<?php
require_once __DIR__ . '/site-content.php';
$novedades_content = site_content('novedades', site_novedades_defaults());
$home_content = site_content('home', site_home_defaults());

$page_title = $novedades_content['page_title'];
$page_label = $novedades_content['page_label'];
$page_intro = $novedades_content['page_intro'];
require_once('page-header.php');
?>
<section class="page-content">
    <div class="container">
        <div class="news-now">
            <div>
                <p class="section-kicker"><?php echo site_escape($novedades_content['social_kicker']); ?></p>
                <h2><?php echo site_escape($novedades_content['social_title']); ?></h2>
                <p><?php echo site_escape($novedades_content['social_intro']); ?></p>
            </div>
            <div class="social-actions">
                <a class="button button--primary" href="<?php echo site_escape($home_content['instagram_url']); ?>" target="_blank" rel="noopener noreferrer"><i class="fa fa-instagram" aria-hidden="true"></i> Instagram</a>
                <a class="button button--primary" href="<?php echo site_escape($home_content['facebook_url']); ?>" target="_blank" rel="noopener noreferrer"><i class="fa fa-facebook" aria-hidden="true"></i> Facebook</a>
            </div>
        </div>
        <div class="archive-heading">
            <p class="section-kicker"><?php echo site_escape($novedades_content['archive_kicker']); ?></p>
            <h2 class="section-title"><?php echo site_escape($novedades_content['archive_title']); ?></h2>
            <p><?php echo site_escape($novedades_content['archive_intro']); ?></p>
        </div>
        <div class="archive-grid">
            <?php foreach ($novedades_content['archive_items'] as $item) { ?>
            <a href="<?php echo site_escape($item['url']); ?>" class="archive-card">
                <img src="<?php echo site_escape($item['image']); ?>" alt="<?php echo site_escape(isset($item['alt']) ? $item['alt'] : $item['title']); ?>">
                <span><?php echo site_escape(isset($item['tag']) ? $item['tag'] : 'Archivo'); ?></span>
                <h3><?php echo site_escape($item['title']); ?></h3>
            </a>
            <?php } ?>
        </div>
    </div>
</section>
<?php require_once('footer.php'); ?>
<script src="js/jquery.js"></script><script src="js/bootstrap.min.js"></script>
</body></html>
