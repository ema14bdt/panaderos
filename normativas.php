<?php
require_once __DIR__ . '/site-content.php';
$normativas_content = site_content('normativas', site_normativas_defaults());

$page_title = $normativas_content['page_title'];
$page_label = $normativas_content['page_label'];
$page_intro = $normativas_content['page_intro'];
require_once('page-header.php');
?>
<section class="page-content">
    <div class="container">
        <p class="section-kicker"><?php echo site_escape($normativas_content['section_kicker']); ?></p>
        <h2 class="section-title"><?php echo site_escape($normativas_content['section_title']); ?></h2>
        <div class="document-grid">
            <?php foreach ($normativas_content['items'] as $item) {
                $cardClass = 'document-card' . (!empty($item['is_featured']) ? ' document-card--feature' : '');
            ?>
            <a class="<?php echo $cardClass; ?>" href="<?php echo site_escape($item['url']); ?>" target="_blank" rel="noopener noreferrer">
                <span><?php echo site_escape($item['kicker']); ?></span>
                <strong><?php echo nl2br(site_escape($item['title'])); ?></strong>
                <i class="fa fa-arrow-up" aria-hidden="true"></i>
            </a>
            <?php } ?>
        </div>
        <div class="document-note">
            <i class="fa fa-info-circle" aria-hidden="true"></i>
            <p><?php echo site_escape($normativas_content['note_text']); ?></p>
            <a class="text-link" href="mailto:<?php echo site_escape($normativas_content['note_email']); ?>"><?php echo site_escape($normativas_content['note_action_text']); ?></a>
        </div>
    </div>
</section>
<?php require_once('footer.php'); ?>
<script src="js/jquery.js"></script><script src="js/bootstrap.min.js"></script>
</body></html>
