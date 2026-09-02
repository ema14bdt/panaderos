<?php
require_once 'site-content.php';
$content = site_content('instalaciones', site_instalaciones_defaults());
$page_title = $content['page_title'];
$page_label = $content['page_label'];
$page_intro = $content['page_intro'];
require_once 'page-header.php';
?>
<section class="page-content">
    <div class="container">
        <p class="section-kicker"><?php echo site_escape($content['section_kicker']); ?></p>
        <h2 class="section-title"><?php echo site_escape($content['section_title']); ?></h2>
        <div class="facility-grid">
            <?php foreach ($content['items'] as $index => $item) {
                $imgPath = isset($item['image']) ? $item['image'] : '';
                $itemTitle = isset($item['title']) && trim($item['title']) !== '' ? $item['title'] : 'Instalaciones del Sindicato de Obreros Panaderos de Lanús';
            ?>
            <a href="<?php echo site_escape($imgPath); ?>" target="_blank" class="facility-item" rel="noopener noreferrer">
                <img src="<?php echo site_escape($imgPath); ?>" alt="<?php echo site_escape($itemTitle); ?>">
                <span><?php echo site_escape($itemTitle); ?> · Imagen <?php echo str_pad($index + 1, 2, '0', STR_PAD_LEFT); ?></span>
            </a>
            <?php } ?>
        </div>
    </div>
</section>
<?php require_once 'footer.php'; ?>
<script src="js/jquery.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="js/main.js"></script>
</body>
</html>
