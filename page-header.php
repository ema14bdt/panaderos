<?php
$page_title = isset($page_title) ? $page_title : 'Panaderos de Lanús';
$page_label = isset($page_label) ? $page_label : 'Sindicato de Obreros Panaderos de Lanús';
$page_intro = isset($page_intro) ? $page_intro : '';
$site_base = isset($site_base) ? $site_base : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo htmlspecialchars($page_title, ENT_QUOTES, 'UTF-8'); ?> | Sindicato de Obreros Panaderos de Lanús">
    <meta name="theme-color" content="#16160f">
    <title><?php echo htmlspecialchars($page_title, ENT_QUOTES, 'UTF-8'); ?> | Panaderos de Lanús</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&display=block" rel="stylesheet">
    <link href="<?php echo $site_base; ?>css/bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo $site_base; ?>css/font-awesome.min.css" rel="stylesheet">
    <link href="<?php echo $site_base; ?>css/main.css" rel="stylesheet">
    <link rel="shortcut icon" href="<?php echo $site_base; ?>images/ico/favicon.ico">
</head>
<body class="site-page">
    <?php require_once(__DIR__ . '/menu.php'); ?>
    <main>
        <section class="page-hero">
            <div class="container">
                <p class="eyebrow"><?php echo htmlspecialchars($page_label, ENT_QUOTES, 'UTF-8'); ?></p>
                <h1><?php echo htmlspecialchars($page_title, ENT_QUOTES, 'UTF-8'); ?></h1>
                <?php if ($page_intro) { ?><p class="page-hero__intro"><?php echo htmlspecialchars($page_intro, ENT_QUOTES, 'UTF-8'); ?></p><?php } ?>
            </div>
        </section>
