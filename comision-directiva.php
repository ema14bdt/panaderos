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
            <table>
                <thead>
                    <tr>
                        <th>Cargo</th>
                        <th>Nombre y apellido</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($comision_content['members'] as $member) { ?>
                    <tr>
                        <td><?php echo site_escape(isset($member['role']) ? $member['role'] : ''); ?></td>
                        <td><?php echo site_escape(isset($member['name']) ? $member['name'] : ''); ?></td>
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
