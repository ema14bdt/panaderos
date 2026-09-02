<?php
$site_base = isset($site_base) ? $site_base : '';
require_once __DIR__ . '/site-content.php';
$site_content = site_content('home', site_home_defaults());

$currentUri = isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : '';
$currentScript = basename($currentUri);
$isComision = ($currentScript === 'comision-directiva.php');
$isServicios = ($currentScript === 'servicios.php');
$isInstalaciones = ($currentScript === 'instalaciones.php');
$isNormativas = ($currentScript === 'normativas.php');
$isFiliales = ($currentScript === 'filiales.php');
$isNovedades = ($currentScript === 'novedades.php' || strpos($currentUri, '/novedades/') !== false || $currentScript === 'novedad.php' || $currentScript === 'novedad-01.php');
?>
<header id="header" class="site-header" role="banner">
    <div class="container">
        <nav class="navbar navbar-inverse" aria-label="Navegación principal">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#site-navigation" aria-expanded="false">
                    <span class="sr-only">Abrir menú</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="site-brand" href="<?php echo $site_base; ?>index.php" aria-label="Panaderos de Lanús, inicio">
                    <img src="<?php echo $site_base; ?>images/logoH.png" alt="">
                    <span class="site-brand__copy"><strong>Panaderos</strong><small>Sindicato de Obreros Panaderos<br>de Lanús</small></span>
                </a>
            </div>
            <div id="site-navigation" class="collapse navbar-collapse">
                <ul class="nav navbar-nav navbar-right">
                    <li class="<?php echo $isComision ? 'active' : ''; ?>"><a href="<?php echo $site_base; ?>comision-directiva.php" <?php echo $isComision ? 'aria-current="page"' : ''; ?>>El sindicato</a></li>
                    <li class="<?php echo $isServicios ? 'active' : ''; ?>"><a href="<?php echo $site_base; ?>servicios.php" <?php echo $isServicios ? 'aria-current="page"' : ''; ?>>Beneficios</a></li>
                    <li class="<?php echo $isInstalaciones ? 'active' : ''; ?>"><a href="<?php echo $site_base; ?>instalaciones.php" <?php echo $isInstalaciones ? 'aria-current="page"' : ''; ?>>Instalaciones</a></li>
                    <li class="<?php echo $isNormativas ? 'active' : ''; ?>"><a href="<?php echo $site_base; ?>normativas.php" <?php echo $isNormativas ? 'aria-current="page"' : ''; ?>>Normativas</a></li>
                    <li class="<?php echo $isFiliales ? 'active' : ''; ?>"><a href="<?php echo $site_base; ?>filiales.php" <?php echo $isFiliales ? 'aria-current="page"' : ''; ?>>Filiales</a></li>
                    <li class="<?php echo $isNovedades ? 'active' : ''; ?>"><a href="<?php echo $site_base; ?>novedades.php" <?php echo $isNovedades ? 'aria-current="page"' : ''; ?>>Actualidad</a></li>
                    <li class="nav-instagram"><a href="<?php echo site_escape($site_content['instagram_url']); ?>" target="_blank" rel="noopener noreferrer"><i class="fa fa-instagram" aria-hidden="true"></i> Instagram</a></li>
                    <li class="nav-facebook"><a href="<?php echo site_escape($site_content['facebook_url']); ?>" target="_blank" rel="noopener noreferrer"><i class="fa fa-facebook" aria-hidden="true"></i> Facebook</a></li>
                </ul>
            </div>
        </nav>
    </div>
</header>
