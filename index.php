<?php
require_once 'site-content.php';
$home_content = site_content('home', site_home_defaults());
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Sindicato de Obreros y Empleados Panaderos de Lanús: derechos, beneficios, servicios e información para trabajadores del sector.">
    <meta name="theme-color" content="#3e4d5f">
    <title>Panaderos de Lanús | Sindicato de Obreros y Empleados Panaderos</title>
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

        <section class="home-main-section" aria-label="Contenido principal y accesos rápidos">
            <div class="container">
                <div class="home-main-layout">
                    <div class="home-main-content">
                    <?php if (!empty($home_content['featured_news_enabled'])) {
                        $featuredImages = array();
                        for ($i = 1; $i <= 3; $i++) {
                            $k = 'featured_news_image_' . $i;
                            if (!empty($home_content[$k])) {
                                $featuredImages[] = $home_content[$k];
                            }
                        }
                        $imgCount = count($featuredImages);
                    ?>
                    <article class="featured-news-card <?php echo $imgCount === 0 ? 'featured-news-card--no-media' : ''; ?>" aria-labelledby="featured-news-title">
                        <div class="featured-news-card__content">
                            <div class="featured-news-card__badge-wrap">
                                <span class="featured-news-card__badge"><i class="fa fa-bullhorn" aria-hidden="true"></i> <?php echo site_escape($home_content['featured_news_badge']); ?></span>
                            </div>
                            <h2 id="featured-news-title" class="featured-news-card__title"><?php echo site_escape($home_content['featured_news_title']); ?></h2>
                            <div class="featured-news-card__lead">
                                <?php echo nl2br(site_escape($home_content['featured_news_description'])); ?>
                            </div>
                            <?php if (!empty($home_content['featured_news_link_url'])) { 
                                $isExternal = (strpos($home_content['featured_news_link_url'], 'http://') === 0 || strpos($home_content['featured_news_link_url'], 'https://') === 0);
                            ?>
                            <div class="featured-news-card__action">
                                <a class="button button--primary" href="<?php echo site_escape($home_content['featured_news_link_url']); ?>" <?php echo $isExternal ? 'target="_blank" rel="noopener noreferrer"' : ''; ?>>
                                    <?php echo site_escape(!empty($home_content['featured_news_link_label']) ? $home_content['featured_news_link_label'] : 'Conocer más'); ?> <i class="fa fa-arrow-right" aria-hidden="true"></i>
                                </a>
                            </div>
                            <?php } ?>
                        </div>
                        <?php if ($imgCount > 0) { ?>
                        <div class="featured-news-card__media featured-news-card__media--count-<?php echo $imgCount; ?>">
                            <?php foreach ($featuredImages as $imgIdx => $imgSrc) { ?>
                            <a class="featured-news-card__img-link" href="<?php echo site_escape($imgSrc); ?>" data-lightbox="home-featured" data-caption="<?php echo site_escape($home_content['featured_news_title']); ?>">
                                <img src="<?php echo site_escape($imgSrc); ?>" alt="<?php echo site_escape($home_content['featured_news_title']) . ' (' . ($imgIdx + 1) . ')'; ?>" loading="lazy">
                                <span class="image-zoom-hint featured-news-card__zoom-hint" title="Ampliar imagen" aria-hidden="true"><i class="fa fa-search-plus"></i></span>
                            </a>
                            <?php } ?>
                        </div>
                        <?php } ?>
                    </article>
                    <?php } ?>

                    <div class="home-intro-block" aria-labelledby="intro-title">
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
                    </div>
                </div>

                <aside class="home-sidebar" aria-label="Gestiones destacadas">
                    <div class="home-sidebar-card">
                        <div class="home-sidebar-block home-sidebar-block--register">
                            <span class="home-sidebar__kicker"><i class="fa fa-briefcase" aria-hidden="true"></i> SP-Online</span>
                            <h3 class="home-sidebar__title">REGISTRATE</h3>
                            <p class="home-sidebar__desc">Registrate al Sistema SP-Online del Sindicato de Obreros y Empleados Panaderos de Lanús.</p>
                            <div class="home-sidebar__actions">
                                <a class="home-sidebar-btn home-sidebar-btn--primary" href="<?php echo site_escape($home_content['register_url']); ?>" target="_blank" rel="noopener noreferrer">
                                    <i class="fa fa-arrow-circle-right" aria-hidden="true"></i> REGISTRARME
                                </a>
                            </div>
                        </div>

                        <div class="home-sidebar-divider" aria-hidden="true"></div>

                        <div class="home-sidebar-block home-sidebar-block--salary">
                            <span class="home-sidebar__kicker"><i class="fa fa-file-pdf-o" aria-hidden="true"></i> Documento oficial</span>
                            <h3 class="home-sidebar__title">ESCALA SALARIAL</h3>
                            <p class="home-sidebar__desc">Último acuerdo salarial vigente homologado para descargar o imprimir.</p>
                            <div class="home-sidebar__links">
                                <a class="home-sidebar-link" href="<?php echo site_escape($home_content['salary_url']); ?>" target="_blank" rel="noopener noreferrer">
                                    <i class="fa fa-download" aria-hidden="true"></i> DESCARGAR
                                </a>
                                <a class="home-sidebar-link" href="<?php echo site_escape($home_content['salary_url']); ?>" target="_blank" rel="noopener noreferrer">
                                    <i class="fa fa-print" aria-hidden="true"></i> IMPRIMIR
                                </a>
                            </div>
                        </div>

                        <div class="home-sidebar-block home-sidebar-block--login">
                            <a class="home-sidebar-btn home-sidebar-btn--accent" href="<?php echo site_escape(!empty($home_content['login_url']) ? $home_content['login_url'] : 'https://panaderoslanus.ar/public/login#sitio-position'); ?>" target="_blank" rel="noopener noreferrer">
                                INGRESAR <i class="fa fa-sign-in" aria-hidden="true"></i>
                            </a>
                            <a class="home-sidebar-sublink" href="https://panaderoslanus.ar/public/password/reset#sitio-position" target="_blank" rel="noopener noreferrer">
                                • Olvidé mi contraseña
                            </a>
                        </div>
                    </div>
                </aside>
                </div>
            </div>
        </section>

        <?php if (!empty($home_content['dia_panadero_enabled'])) { 
            $dpImage = !empty($home_content['dia_panadero_image']) ? $home_content['dia_panadero_image'] : 'images/dia-panaderos.png';
            $dpAudio = !empty($home_content['dia_panadero_audio']) ? $home_content['dia_panadero_audio'] : '';
            $dpDesc = !empty($home_content['dia_panadero_description']) ? $home_content['dia_panadero_description'] : '';
            $dpLyrics = !empty($home_content['dia_panadero_lyrics']) ? $home_content['dia_panadero_lyrics'] : '';
        ?>
        <section class="dia-panadero-section" aria-labelledby="dia-panadero-title">
            <div class="container">
                <div class="dia-panadero-card">
                    <div class="dia-panadero-card__header">
                        <span class="dia-panadero-badge"><i class="fa fa-calendar-check-o" aria-hidden="true"></i> <?php echo site_escape(!empty($home_content['dia_panadero_badge']) ? $home_content['dia_panadero_badge'] : '4 de Agosto · Conmemoración'); ?></span>
                        <h2 id="dia-panadero-title" class="dia-panadero-title"><?php echo site_escape(!empty($home_content['dia_panadero_title']) ? $home_content['dia_panadero_title'] : '4 DE AGOSTO - DÍA DEL OBRERO PANADERO'); ?></h2>
                    </div>

                    <div class="dia-panadero-grid">
                        <div class="dia-panadero-content">
                            <div class="dia-panadero-prose">
                                <?php echo nl2br(site_escape($dpDesc)); ?>
                            </div>

                            <?php if ($dpAudio !== '') { ?>
                            <div class="dia-panadero-player">
                                <div class="dia-panadero-player__meta">
                                    <span class="dia-panadero-player__icon"><i class="fa fa-music" aria-hidden="true"></i></span>
                                    <div class="dia-panadero-player__text">
                                        <strong><?php echo site_escape(!empty($home_content['dia_panadero_audio_title']) ? $home_content['dia_panadero_audio_title'] : 'Marcha de los panaderos'); ?></strong>
                                        <small>Autor: <?php echo site_escape(!empty($home_content['dia_panadero_audio_author']) ? $home_content['dia_panadero_audio_author'] : 'Rodriguez Lobos'); ?></small>
                                    </div>
                                </div>
                                <audio controls preload="metadata" class="dia-panadero-audio">
                                    <source src="<?php echo site_escape($dpAudio); ?>" type="audio/mpeg">
                                    Tu navegador no soporta reproducción de audio HTML5.
                                </audio>
                            </div>
                            <?php } ?>

                            <?php if ($dpLyrics !== '') { ?>
                            <div class="dia-panadero-lyrics-action">
                                <button type="button" class="dia-panadero-lyrics-trigger" id="btn-open-lyrics" aria-haspopup="dialog" aria-controls="lyrics-lightbox">
                                    <i class="fa fa-file-text-o" aria-hidden="true"></i>
                                    <span>Ver letra completa de la marcha</span>
                                    <i class="fa fa-external-link" aria-hidden="true"></i>
                                </button>
                            </div>
                            <?php } ?>
                        </div>

                        <?php if ($dpImage !== '') { ?>
                        <div class="dia-panadero-media">
                            <div class="dia-panadero-sheet">
                                <a href="<?php echo site_escape($dpImage); ?>" data-lightbox="dia-panadero" data-caption="4 de Agosto - Marcha de los Panaderos · Rodríguez Lobos" class="dia-panadero-sheet__link">
                                    <picture>
                                        <source srcset="images/dia-panaderos.webp" type="image/webp">
                                        <img src="<?php echo site_escape($dpImage); ?>" alt="Partitura y poema de la Marcha de los Panaderos" loading="lazy">
                                    </picture>
                                    <span class="image-zoom-hint" title="Ampliar documento" aria-hidden="true"><i class="fa fa-search-plus"></i></span>
                                </a>
                                <p class="dia-panadero-sheet__caption"><i class="fa fa-history" aria-hidden="true"></i> Partitura histórica original · Rodríguez Lobos</p>
                            </div>
                        </div>
                        <?php } ?>
                    </div>
                </div>
            </div>

            <?php if ($dpLyrics !== '') { ?>
            <div id="lyrics-lightbox" class="lyrics-lightbox" role="dialog" aria-modal="true" aria-labelledby="lyrics-lightbox-title" aria-hidden="true">
                <div class="lyrics-lightbox__backdrop"></div>
                <div class="lyrics-lightbox__container">
                    <div class="lyrics-lightbox__header">
                        <div class="lyrics-lightbox__meta">
                            <span id="lyrics-lightbox-title" class="lyrics-lightbox__caption">Marcha de los Panaderos</span>
                            <span class="lyrics-lightbox__counter">Letra oficial · Rodríguez Lobos</span>
                        </div>
                        <button type="button" class="lyrics-lightbox__close" id="lyrics-lightbox-close" aria-label="Cerrar letra">&times;</button>
                    </div>
                    <div class="lyrics-lightbox__stage">
                        <div class="lyrics-lightbox__dialog">
                            <div class="lyrics-lightbox__card">
                                <div class="lyrics-lightbox__card-header">
                                    <span class="lyrics-lightbox__badge"><i class="fa fa-book" aria-hidden="true"></i> Himno y Poema Histórico</span>
                                    <h3 class="lyrics-lightbox__heading">Marcha de los Panaderos</h3>
                                    <p class="lyrics-lightbox__subheading">Autor: <strong>Rodríguez Lobos</strong></p>
                                </div>
                                <div class="lyrics-lightbox__card-body">
                                    <?php echo nl2br(site_escape($dpLyrics)); ?>
                                </div>
                                <div class="lyrics-lightbox__card-footer">
                                    <button type="button" class="lyrics-lightbox__btn-close" id="lyrics-lightbox-close-bottom">Cerrar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php } ?>
        </section>
        <?php } ?>

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
