<?php
$site_base = '../../';
$page_title = 'Asamblea General Ordinaria 2015';
$page_label = 'Archivo histórico';
$page_intro = 'Una instancia de encuentro y participación de las y los compañeros del sindicato.';
require_once('../../page-header.php');
?>
<section class="page-content archive-story"><div class="container"><p class="archive-story__text">La Asamblea General Ordinaria reunió a compañeros y autoridades para compartir un encuentro institucional. Participó el Secretario General a nivel nacional de FAUPPA, Abel Frutos.</p><div class="facility-grid facility-grid--story"><?php foreach (array('01.jpg', '02.jpg', '03.jpg', '04.jpg', '05.jpg') as $index => $image) { ?><a href="fotos/full/<?php echo $image; ?>" target="_blank" class="facility-item"><img src="fotos/full/<?php echo $image; ?>" alt="Asamblea General Ordinaria 2015"><span>Ver imagen <?php echo str_pad($index + 1, 2, '0', STR_PAD_LEFT); ?></span></a><?php } ?></div><a class="text-link" href="../../novedades.php"><i class="fa fa-arrow-left" aria-hidden="true"></i> Volver al archivo</a></div></section>
<?php require_once('../../footer.php'); ?>
<script src="../../js/jquery.js"></script><script src="../../js/bootstrap.min.js"></script><script src="../../js/main.js"></script>
</body></html>
