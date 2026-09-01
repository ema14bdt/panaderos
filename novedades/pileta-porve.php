<?php
$site_base = '../';
$page_title = 'Jornada en familia en las piletas del Porve';
$page_label = 'Archivo histórico';
$page_intro = 'Un encuentro de afiliados y familias para compartir el orgullo por el oficio panadero.';
require_once('../page-header.php');
?>
<section class="page-content archive-story"><div class="container"><p class="archive-story__text">En una jornada de verano, el sindicato compartió un día de encuentro con sus afiliados en las piletas del Club El Porvenir. Fue un espacio para disfrutar, conversar y celebrar el vínculo entre compañeros de distintas panaderías de Lanús.</p><div class="facility-grid facility-grid--story"><?php foreach (array('porve-01.jpg', 'porve-02.jpg', 'porve-03.jpg', 'porve-04.jpg') as $index => $image) { ?><a href="../images/novedades/pileta-porve/full/<?php echo $image; ?>" target="_blank" class="facility-item"><img src="../images/novedades/pileta-porve/full/<?php echo $image; ?>" alt="Jornada familiar de Panaderos de Lanús"><span>Ver imagen <?php echo str_pad($index + 1, 2, '0', STR_PAD_LEFT); ?></span></a><?php } ?></div><a class="text-link" href="../novedades.php"><i class="fa fa-arrow-left" aria-hidden="true"></i> Volver al archivo</a></div></section>
<?php require_once('../footer.php'); ?>
<script src="../js/jquery.js"></script><script src="../js/bootstrap.min.js"></script>
</body></html>
