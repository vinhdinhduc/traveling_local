<?php

$pageTitle = 'Trang không tồn tại';
require_once 'includes/header.php';
?>

<div class="page-404">
    <h1>404</h1>
    <h2>Trang không tồn tại</h2>
    <p>Xin lỗi, trang bạn đang tìm kiếm không tồn tại hoặc đã bị xóa.</p>
    <a href="<?= SITE_URL ?>" class="btn btn-primary">
        <i class="fas fa-home"></i> Về trang chủ
    </a>
</div>

<?php require_once 'includes/footer.php'; ?>