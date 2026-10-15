<?php
require_once 'site-content.php';
$content = site_content('instalaciones', site_instalaciones_defaults());
$page_title = $content['page_title'];
$page_label = $content['page_label'];
$page_intro = $content['page_intro'];
require_once 'page-header.php';

$reels = isset($content['reels']) && is_array($content['reels']) ? $content['reels'] : array();
$validReels = array();
foreach ($reels as $r) {
    if (isset($r['video']) && trim((string)$r['video']) !== '') {
        $validReels[] = $r;
    }
}
?>
<section class="page-content">
    <div class="container">
        <?php if (!empty($validReels)) { ?>
        <div class="reels-section">
            <p class="section-kicker"><?php echo site_escape(isset($content['reels_kicker']) && trim((string)$content['reels_kicker']) !== '' ? $content['reels_kicker'] : 'En video'); ?></p>
            <h2 class="section-title"><?php echo site_escape(isset($content['reels_title']) && trim((string)$content['reels_title']) !== '' ? $content['reels_title'] : 'Recorré las instalaciones'); ?></h2>
            <div class="reels-grid">
                <?php foreach ($validReels as $idx => $reel) {
                    $videoSrc = $reel['video'];
                    $posterSrc = isset($reel['poster']) ? $reel['poster'] : '';
                    $reelTitle = isset($reel['title']) && trim((string)$reel['title']) !== '' ? $reel['title'] : 'Instalaciones';
                ?>
                <article class="reel-card" data-reel-id="<?php echo $idx; ?>">
                    <div class="reel-card__video-wrap">
                        <video class="reel-card__video" 
                               src="<?php echo site_escape($videoSrc); ?>" 
                               <?php if ($posterSrc !== '') { ?>poster="<?php echo site_escape($posterSrc); ?>"<?php } ?>
                               preload="metadata" 
                               playsinline 
                               muted 
                               loop>
                        </video>
                        <div class="reel-card__overlay">
                            <button type="button" class="reel-card__play-btn" aria-label="Reproducir video"><i class="fa fa-play" aria-hidden="true"></i></button>
                            <button type="button" class="reel-card__sound-btn" aria-label="Activar o desactivar sonido"><i class="fa fa-volume-off" aria-hidden="true"></i></button>
                        </div>
                    </div>
                    <div class="reel-card__footer">
                        <span class="reel-card__kicker"><i class="fa fa-video-camera" aria-hidden="true"></i> Reel</span>
                        <h3 class="reel-card__title"><?php echo site_escape($reelTitle); ?></h3>
                    </div>
                </article>
                <?php } ?>
            </div>
        </div>
        <hr class="facilities-separator">
        <?php } ?>

        <p class="section-kicker"><?php echo site_escape($content['section_kicker']); ?></p>
        <h2 class="section-title"><?php echo site_escape($content['section_title']); ?></h2>
        <div class="facility-grid">
            <?php foreach ($content['items'] as $index => $item) {
                $imgPath = isset($item['image']) ? $item['image'] : '';
                $itemTitle = isset($item['title']) && trim($item['title']) !== '' ? $item['title'] : 'Instalaciones del Sindicato de Obreros y Empleados Panaderos de Lanús';
            ?>
            <a href="<?php echo site_escape($imgPath); ?>" target="_blank" class="facility-item" rel="noopener noreferrer">
                <img src="<?php echo site_escape($imgPath); ?>" alt="<?php echo site_escape($itemTitle); ?>">
                <span><?php echo site_escape($itemTitle); ?> · Imagen <?php echo str_pad($index + 1, 2, '0', STR_PAD_LEFT); ?></span>
                <span class="image-zoom-hint facility-item__zoom-hint" title="Ampliar imagen" aria-hidden="true"><i class="fa fa-search-plus"></i></span>
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
