</div><!-- /.admin-content -->
</div><!-- /.admin-main -->
</div><!-- /.admin-wrapper -->

<!-- Admin JS -->
<script src="<?= SITE_URL ?>/assets/js/admin.js"></script>

<?php
$adminFooterPage = basename($_SERVER['PHP_SELF'], '.php');
$adminFooterDir = basename(dirname($_SERVER['PHP_SELF']));
$adminFooterKey = ($adminFooterDir === 'admin') ? $adminFooterPage : ($adminFooterDir . '-' . $adminFooterPage);
$safeAdminFooterKey = preg_replace('/[^a-z0-9\-]/i', '', $adminFooterKey);
$adminPageJsRel = '/assets/js/admin-pages/' . $safeAdminFooterKey . '.js';
$adminPageJsAbs = dirname(dirname(__DIR__)) . $adminPageJsRel;
?>

<?php
if (isset($adminScripts) && is_array($adminScripts)):
    foreach ($adminScripts as $scriptPath):
        $scriptPath = (string)$scriptPath;
        if ($scriptPath !== '' && (str_starts_with($scriptPath, '/') || preg_match('#^https?://#i', $scriptPath))):
            $scriptSrc = str_starts_with($scriptPath, '/') ? (SITE_URL . $scriptPath) : $scriptPath;
?>
            <script src="<?= $scriptSrc ?>"></script>
<?php
        endif;
    endforeach;
endif;
?>

<?php if ($safeAdminFooterKey !== '' && file_exists($adminPageJsAbs)): ?>
    <script src="<?= SITE_URL . $adminPageJsRel ?>"></script>
<?php endif; ?>

</body>

</html>