<?php
require_once __DIR__ . '/site-content.php';
$site_content = site_content('home', site_home_defaults());
?>
<aside class="utility-panel"><p class="section-kicker">Gestiones online</p><h3>Todo lo que necesitás para hacer tus trámites.</h3><a class="button button--primary" href="<?php echo site_escape($site_content['register_url']); ?>" target="_blank" rel="noopener noreferrer">Registrarme <i class="fa fa-arrow-right" aria-hidden="true"></i></a><a class="text-link" href="<?php echo site_escape($site_content['salary_url']); ?>" target="_blank" rel="noopener noreferrer">Descargar escala salarial <i class="fa fa-download" aria-hidden="true"></i></a></aside>
