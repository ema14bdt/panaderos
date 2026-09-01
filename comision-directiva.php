<?php
require_once __DIR__ . '/site-content.php';
$comision_content = site_content('comision', site_comision_defaults());

$page_title = $comision_content['page_title'];
$page_label = $comision_content['page_label'];
$page_intro = $comision_content['page_intro'];
require_once('page-header.php');
?>
<section class="page-content">
    <div class="container">
        <p class="section-kicker"><?php echo site_escape($comision_content['section_kicker']); ?></p>
        <h2 class="section-title"><?php echo site_escape($comision_content['section_title']); ?></h2>
        <div class="committee-grid">
            <?php foreach ($comision_content['members'] as $index => $member) {
                $photo = !empty($member['photo']) ? site_escape($member['photo']) : 'images/directivos/sin-foto.jpg';
            ?>
            <article class="committee-member">
                <span><?php echo str_pad($index + 1, 2, '0', STR_PAD_LEFT); ?></span>
                <div class="committee-member__portrait">
                    <img src="<?php echo $photo; ?>" alt="<?php echo site_escape(isset($member['name']) ? $member['name'] : ''); ?>">
                </div>
                <h3><?php echo site_escape(isset($member['name']) ? $member['name'] : ''); ?></h3>
                <p><?php echo site_escape(isset($member['role']) ? $member['role'] : ''); ?></p>
            </article>
            <?php } ?>
        </div>
    </div>
</section>
<?php require_once('footer.php'); ?>
<script src="js/jquery.js"></script><script src="js/bootstrap.min.js"></script>
</body></html>
