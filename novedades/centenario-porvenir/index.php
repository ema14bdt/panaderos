<?php
$site_base = '../../';
$page_title = 'Centenario del Club El Porvenir';
$page_label = 'Archivo histórico';
$page_intro = 'El centenario del club fue declarado de interés cultural y deportivo por el HCD de Lanús.';
require_once('../../page-header.php');
?>
<section class="page-content archive-story"><div class="container"><p class="archive-story__text">En una conferencia de prensa se presentó el proyecto que declaró de interés cultural y deportivo el centenario del Club El Porvenir. La actividad contó con la participación de referentes locales y de la organización panadera.</p><div class="facility-grid facility-grid--story"><?php foreach (array('01.jpg', '02.jpg', '03.jpg') as $index => $image) { ?><a href="fotos/<?php echo $image; ?>" target="_blank" class="facility-item"><img src="fotos/<?php echo $image; ?>" alt="Centenario del Club El Porvenir"><span>Ver imagen <?php echo str_pad($index + 1, 2, '0', STR_PAD_LEFT); ?></span></a><?php } ?></div><a class="text-link" href="../../novedades.php"><i class="fa fa-arrow-left" aria-hidden="true"></i> Volver al archivo</a></div></section>
<?php require_once('../../footer.php'); ?>
<script src="../../js/jquery.js"></script><script src="../../js/bootstrap.min.js"></script>
</body></html>
