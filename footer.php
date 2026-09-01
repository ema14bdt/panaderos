<?php
require_once __DIR__ . '/site-content.php';
$site_content = site_content('home', site_home_defaults());
$footer_phone = preg_replace('/[^0-9+]/', '', $site_content['phone']);
?>
<footer id="footer" class="site-footer">
    <div class="box">
        <div class="container site-footer__inner">
            <a class="site-footer__brand" href="<?php echo $site_base; ?>index.php">
                <img src="<?php echo $site_base; ?>images/logoH.png" alt="">
                <span><strong>Panaderos</strong><small>Sindicato de Obreros<br>de Lanús</small></span>
            </a>
            <p class="site-footer__message">Organización, derechos y acompañamiento para las y los trabajadores panaderos.</p>
            <div class="site-footer__contact"><a href="tel:<?php echo site_escape($footer_phone); ?>"><i class="fa fa-phone" aria-hidden="true"></i> <?php echo site_escape($site_content['phone_label']); ?></a><a href="mailto:<?php echo site_escape($site_content['email']); ?>"><i class="fa fa-envelope" aria-hidden="true"></i> <?php echo site_escape($site_content['email']); ?></a></div>
            <span class="site-footer__legal"><?php echo site_escape($site_content['address']); ?> · © <?php echo date('Y'); ?> Sindicato de Obreros Panaderos de Lanús</span>
        </div>
    </div>
</footer>
