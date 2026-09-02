<?php
require_once 'site-content.php';
$home_content = site_content('home', site_home_defaults());
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Sindicato de Obreros Panaderos de Lanús: derechos, beneficios, servicios e información para trabajadores del sector.">
    <meta name="theme-color" content="#15140f">
    <title>Panaderos de Lanús | Sindicato de Obreros Panaderos</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&display=block" rel="stylesheet">
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/font-awesome.min.css" rel="stylesheet">
    <link href="css/main.css" rel="stylesheet">
    <!--[if lt IE 9]>
    <script src="js/html5shiv.js"></script>
    <script src="js/respond.min.js"></script>
    <![endif]-->
    <link rel="shortcut icon" href="images/ico/favicon.ico">
</head>

<body class="home-2026">
    <?php require_once('menu.php'); ?>

    <main>
        <section class="home-hero" aria-labelledby="hero-title">
            <div class="home-hero__grain" aria-hidden="true"></div>
            <div class="container home-hero__layout">
                <div class="home-hero__copy">
                    <p class="eyebrow"><?php echo site_escape($home_content['hero_eyebrow']); ?></p>
                    <h1 id="hero-title"><?php echo site_escape($home_content['hero_title']); ?><br><em><?php echo site_escape($home_content['hero_emphasis']); ?></em> <?php echo site_escape($home_content['hero_after']); ?></h1>
                    <p class="home-hero__lead"><?php echo site_escape($home_content['hero_lead']); ?></p>
                    <div class="home-actions">
                        <a class="button button--primary" href="servicios.php">Ver beneficios <i class="fa fa-arrow-right" aria-hidden="true"></i></a>
                        <a class="button button--quiet" href="<?php echo site_escape($home_content['instagram_url']); ?>" target="_blank" rel="noopener noreferrer" aria-label="Instagram"><i class="fa fa-instagram" aria-hidden="true"></i><span class="button__label">Instagram</span></a>
                        <a class="button button--quiet" href="<?php echo site_escape($home_content['facebook_url']); ?>" target="_blank" rel="noopener noreferrer" aria-label="Facebook"><i class="fa fa-facebook" aria-hidden="true"></i><span class="button__label">Facebook</span></a>
                    </div>
                </div>
                <aside class="home-hero__statement" aria-label="Principio del sindicato">
                    <span class="statement-mark" aria-hidden="true">“</span>
                    <p><?php echo site_escape($home_content['hero_quote']); ?></p>
                    <span class="statement-author"><?php echo site_escape($home_content['hero_quote_author']); ?></span>
                </aside>
            </div>
            <div class="home-hero__rule container" aria-hidden="true"><span>LANÚS · BUENOS AIRES</span><span>DESDE EL TRABAJO, PARA EL TRABAJO</span></div>
        </section>

        <section class="home-intro" aria-labelledby="intro-title">
            <div class="container">
                <div class="section-heading">
                    <p class="eyebrow">Estar cerca también es informar</p>
                    <h2 id="intro-title">Todo lo que necesitás, <em>en un solo lugar.</em></h2>
                </div>
                <div class="home-guides">
                    <a class="guide-card guide-card--dark" href="servicios.php">
                        <span class="guide-card__number">01</span>
                        <i class="fa fa-heart-o" aria-hidden="true"></i>
                        <h3>Beneficios para afiliados</h3>
                        <p>Salud, farmacia, nacimiento, casamiento y mudanza: conocé los servicios disponibles.</p>
                        <span class="guide-card__link">Conocer servicios <i class="fa fa-long-arrow-right" aria-hidden="true"></i></span>
                    </a>
                    <a class="guide-card guide-card--sun" href="normativas.php">
                        <span class="guide-card__number">02</span>
                        <i class="fa fa-file-text-o" aria-hidden="true"></i>
                        <h3>Normativas y escalas</h3>
                        <p>Accedé a convenios, escalas salariales y documentación para consultar tus derechos.</p>
                        <span class="guide-card__link">Ver documentos <i class="fa fa-long-arrow-right" aria-hidden="true"></i></span>
                    </a>
                    <a class="guide-card guide-card--paper" href="filiales.php">
                        <span class="guide-card__number">03</span>
                        <i class="fa fa-map-marker" aria-hidden="true"></i>
                        <h3>Una red federal</h3>
                        <p>Encontrá las filiales y los datos de contacto para resolver tus consultas.</p>
                        <span class="guide-card__link">Ver filiales <i class="fa fa-long-arrow-right" aria-hidden="true"></i></span>
                    </a>
                </div>
                <a class="salary-banner" href="<?php echo site_escape($home_content['salary_url']); ?>" target="_blank" rel="noopener noreferrer">
                    <span class="salary-banner__badge"><i class="fa fa-file-pdf-o" aria-hidden="true"></i> Documento vigente</span>
                    <span class="salary-banner__title">Escala salarial vigente</span>
                    <span class="salary-banner__action">Descargar PDF <i class="fa fa-arrow-up" aria-hidden="true"></i></span>
                </a>
            </div>
        </section>

        <section class="social-brief" aria-labelledby="social-title">
            <div class="container social-brief__layout">
                <div class="social-brief__heading">
                    <p class="eyebrow">Canales oficiales</p>
                    <h2 id="social-title">La actualidad del gremio,<br><em>en tu pantalla.</em></h2>
                </div>
                <div class="social-brief__body">
                    <p><?php echo site_escape($home_content['social_intro']); ?></p>
                    <ul class="social-topics" aria-label="Temas publicados en redes sociales">
                        <li>Defensa del empleo</li>
                        <li>Información gremial</li>
                        <li>Actividades y beneficios</li>
                    </ul>
                    <div class="social-links"><a class="social-handle" href="<?php echo site_escape($home_content['instagram_url']); ?>" target="_blank" rel="noopener noreferrer"><i class="fa fa-instagram" aria-hidden="true"></i> <?php echo site_escape($home_content['instagram_label']); ?> <span>Seguir <i class="fa fa-arrow-up" aria-hidden="true"></i></span></a><a class="social-handle" href="<?php echo site_escape($home_content['facebook_url']); ?>" target="_blank" rel="noopener noreferrer"><i class="fa fa-facebook" aria-hidden="true"></i> <?php echo site_escape($home_content['facebook_label']); ?> <span>Seguir <i class="fa fa-arrow-up" aria-hidden="true"></i></span></a></div>
                </div>
            </div>
        </section>

        <section class="location-section" aria-labelledby="location-title">
            <div class="container location-section__layout">
                <div class="location-section__copy">
                    <p class="eyebrow">Dónde encontrarnos</p>
                    <h2 id="location-title">La sede, <em>cerca.</em></h2>
                    <p><?php echo site_escape($home_content['address']); ?></p>
                </div>
                <div class="location-map">
                    <iframe title="Ubicación del Sindicato de Obreros y Empleados Panaderos de Lanús" src="<?php echo site_escape($home_content['maps_embed_url']); ?>" loading="lazy" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
                </div>
            </div>
        </section>
    </main>

    <?php require_once('footer.php'); ?>

    <script src="js/jquery.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/main.js"></script>
</body>
</html>
