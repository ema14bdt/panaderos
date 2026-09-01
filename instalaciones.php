<?php
$page_title = 'Nuestras instalaciones';
$page_label = 'Espacios para encontrarnos';
$page_intro = 'Conocé los espacios que forman parte de la vida cotidiana del sindicato y de sus afiliados.';
$facility_images = array('instalaciones-01.jpg', 'instalaciones-02.jpg', 'instalaciones-03.jpg', 'instalaciones-04.jpg', 'instalaciones-05.jpg', 'instalaciones-06.jpg');
require_once('page-header.php');
?>
<section class="page-content">
    <div class="container">
        <p class="section-kicker">Galería</p><h2 class="section-title">Un lugar para estar cerca.</h2>
        <div class="facility-grid">
            <?php foreach ($facility_images as $index => $image) { ?>
            <a href="images/instalaciones/<?php echo $image; ?>" target="_blank" class="facility-item"><img src="images/instalaciones/<?php echo $image; ?>" alt="Instalaciones del Sindicato de Obreros Panaderos de Lanús"><span>Ver imagen <?php echo str_pad($index + 1, 2, '0', STR_PAD_LEFT); ?></span></a>
            <?php } ?>
        </div>
    </div>
</section>
<?php require_once('footer.php'); ?>
<script src="js/jquery.js"></script><script src="js/bootstrap.min.js"></script>
</body></html>
