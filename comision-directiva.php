<?php
require_once __DIR__ . '/site-content.php';
$comision_content = site_content('comision', site_comision_defaults());

$page_title = $comision_content['page_title'];
$page_label = $comision_content['page_label'];
$page_intro = $comision_content['page_intro'];
require_once('page-header.php');
?>
<section class="page-content">
    <div class="container">
        <p class="section-kicker"><?php echo site_escape($comision_content['section_kicker']); ?></p>
        <h2 class="section-title"><?php echo site_escape($comision_content['section_title']); ?></h2>
        <div class="branch-table branch-table--committee" role="region" aria-label="Nómina de Comisión Directiva" tabindex="0">
            <table role="table">
                <thead>
                    <tr role="row">
                        <th role="columnheader">Cargo</th>
                        <th role="columnheader">Nombre y apellido</th>
                    </tr>
                </thead>
                <tbody role="rowgroup">
                    <?php foreach ($comision_content['members'] as $member) { ?>
                    <tr role="row">
                        <td role="cell" data-label="Cargo"><?php echo site_escape(isset($member['role']) ? $member['role'] : ''); ?></td>
                        <td role="cell" data-label="Nombre y apellido"><?php echo site_escape(isset($member['name']) ? $member['name'] : ''); ?></td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
<?php require_once('footer.php'); ?>
<script src="js/jquery.js"></script><script src="js/bootstrap.min.js"></script><script src="js/main.js"></script>
</body></html>
