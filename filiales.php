<?php
require_once __DIR__ . '/site-content.php';
$filiales_content = site_content('filiales', site_filiales_defaults());

$page_title = $filiales_content['page_title'];
$page_label = $filiales_content['page_label'];
$page_intro = $filiales_content['page_intro'];
require_once('page-header.php');
?>
<section class="page-content">
    <div class="container">
        <p class="section-kicker"><?php echo site_escape($filiales_content['section_kicker']); ?></p>
        <h2 class="section-title"><?php echo site_escape($filiales_content['section_title']); ?></h2>
        <div class="branch-table" role="region" aria-label="Directorio de filiales" tabindex="0">
            <table>
                <thead>
                    <tr>
                        <th>Filial</th>
                        <th>Dirección</th>
                        <th>Teléfono</th>
                        <th>Secretaría general</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($filiales_content['items'] as $branch) { ?>
                    <tr>
                        <td><?php echo site_escape(isset($branch['name']) ? $branch['name'] : ''); ?></td>
                        <td><?php echo site_escape(isset($branch['address']) ? $branch['address'] : ''); ?></td>
                        <td><?php echo site_escape(isset($branch['phone']) ? $branch['phone'] : ''); ?></td>
                        <td><?php echo site_escape(isset($branch['secretary']) ? $branch['secretary'] : ''); ?></td>
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
