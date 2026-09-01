<?php
$site_base = '../../';
$page_title = 'El sindicato marchó por #NiUnaMenos';
$page_label = 'Archivo histórico';
$page_intro = 'Una movilización contra los femicidios y la violencia de género hacia las mujeres.';
$photos = array('01.jpg', '02.jpg', '03.jpg', '04.jpg', '05.jpg', '06.jpg', '07.jpg', '08.jpg', '09.jpg', '10.jpg', '11.jpg', '12.jpg', '13.jpg', '14.jpg', '15.jpg', '16.jpg', '17.jpg', '18.jpg');
require_once('../../page-header.php');
?>
<section class="page-content archive-story"><div class="container"><p class="archive-story__text">El Sindicato de Obreros Panaderos de Lanús participó de la movilización bajo la consigna #NiUnaMenos, junto a organizaciones sociales, referentes y trabajadores, para acompañar el reclamo contra la violencia de género.</p><div class="facility-grid facility-grid--story"><?php foreach ($photos as $index => $image) { ?><a href="fotos/<?php echo $image; ?>" target="_blank" class="facility-item"><img src="fotos/<?php echo $image; ?>" alt="Movilización Ni Una Menos"><span>Ver imagen <?php echo str_pad($index + 1, 2, '0', STR_PAD_LEFT); ?></span></a><?php } ?></div><a class="text-link" href="../../novedades.php"><i class="fa fa-arrow-left" aria-hidden="true"></i> Volver al archivo</a></div></section>
<?php require_once('../../footer.php'); ?>
<script src="../../js/jquery.js"></script><script src="../../js/bootstrap.min.js"></script>
</body></html>
