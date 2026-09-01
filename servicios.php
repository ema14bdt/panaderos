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
                $cardClass = 'content-card' . (!empty($item['theme']) ? ' content-card--' . site_escape($item['theme']) : '');
                $iconClass = !empty($item['icon']) ? site_escape($item['icon']) : 'fa-check';
            ?>
            <article class="<?php echo $cardClass; ?>">
                <i class="fa <?php echo $iconClass; ?>" aria-hidden="true"></i>
                <h3><?php echo site_escape($item['title']); ?></h3>
                <p><?php echo site_escape($item['description']); ?></p>
                <span class="benefit-detail"><?php echo nl2br(site_escape($item['detail'])); ?></span>
            </article>
            <?php } ?>
            <aside class="benefits-callout">
                <p class="section-kicker"><?php echo site_escape($servicios_content['callout_kicker']); ?></p>
                <h3><?php echo site_escape($servicios_content['callout_title']); ?></h3>
                <a class="text-link" href="mailto:<?php echo site_escape($servicios_content['callout_email']); ?>"><?php echo site_escape($servicios_content['callout_action_text']); ?> <i class="fa fa-arrow-right" aria-hidden="true"></i></a>
            </aside>
        </div>
    </div>
</section>
<?php require_once('footer.php'); ?>
<script src="js/jquery.js"></script><script src="js/bootstrap.min.js"></script>
</body></html>
