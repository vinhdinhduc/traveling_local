<?php
require_once '../../includes/config.php';
require_once dirname(__DIR__, 2) . '/functions.php';
requireUserLogin();

$currentUser = getCurrentUser($pdo);
if ($currentUser['role'] !== 'admin') {
    setFlash('error', 'Bạn không có quyền.');
    header('Location: ' . SITE_URL);
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
        $errors[] = 'Phiên làm việc không hợp lệ.';
    } else {
        $name      = trim($_POST['name'] ?? '');
        $icon      = trim($_POST['icon'] ?? '');
        $sortOrder = (int)($_POST['sort_order'] ?? 0);

        if ($name === '') {
            $errors[] = 'Vui lòng nhập tên tiện nghi.';
        }
        if ($icon === '') {
            $errors[] = 'Vui lòng nhập icon class.';
        }

        if (empty($errors)) {
            $stmt = $pdo->prepare('INSERT INTO amenities (name, icon, sort_order) VALUES (?, ?, ?)');
            $stmt->execute([$name, $icon, $sortOrder]);
            
            setFlash('success', 'Đã thêm tiện nghi thành công.');
            header('Location: index.php');
            exit;
        }
    }
}

$pageTitle = 'Thêm Tiện nghi';
require_once '../includes/header.php';
?>

<div class="admin-wrapper">
    <?php require_once '../includes/sidebar.php'; ?>

    <main class="admin-main">
        <div class="admin-header">
            <h2><i class="fas fa-plus"></i> Thêm Tiện nghi mới</h2>
            <div class="admin-user-info">
                <span>Xin chào, <strong><?= sanitize($currentUser['full_name']) ?></strong></span>
                <i class="fas fa-user-circle"></i>
            </div>
        </div>

        <div class="admin-content fade-in">
            <?= getFlash() ?>
            
            <?php if (!empty($errors)): ?>
                <div class="alert alert-error" style="background:#fde8e8;color:#721c24;padding:12px;border-radius:4px;margin-bottom:15px; border-left:4px solid #e53935;">
                    <?php foreach ($errors as $e): ?>
                        <div><?= sanitize($e) ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="admin-card" style="max-width: 600px;">
                <form method="POST" action="">
                    <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">

                    <div class="form-group" style="margin-bottom: 20px;">
                        <label style="font-weight: 600; display:block; margin-bottom: 6px;">Tên tiện nghi *</label>
                        <input type="text" name="name" class="form-control" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px;" placeholder="VD: Wifi miễn phí, Bể bơi..." value="<?= sanitize($_POST['name'] ?? '') ?>" required>
                    </div>

                    <div class="form-group" style="margin-bottom: 20px;">
                        <label style="font-weight: 600; display:block; margin-bottom: 6px;">Icon FontAwesome *</label>
                        <input type="text" name="icon" class="form-control" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px;" placeholder="VD: fa-wifi, fa-swimming-pool" value="<?= sanitize($_POST['icon'] ?? '') ?>" required>
                        <small style="color:#777;">Tìm class icon tại <a href="https://fontawesome.com/v5/search?m=free" target="_blank">FontAwesome</a> (chỉ lấy phần sau fa, ví dụ `fa-coffee`).</small>
                    </div>

                    <div class="form-group" style="margin-bottom: 20px;">
                        <label style="font-weight: 600; display:block; margin-bottom: 6px;">Thứ tự hiển thị</label>
                        <input type="number" name="sort_order" class="form-control" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px;" placeholder="0" value="<?= sanitize($_POST['sort_order'] ?? '0') ?>">
                    </div>

                    <div style="margin-top: 10px; border-top: 1px solid #eee; padding-top: 20px;">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Thêm tiện nghi</button>
                        <a href="index.php" class="btn btn-outline" style="background:#fff; color:#333; border: 1px solid #ccc; padding: 12px 24px; border-radius:8px; text-decoration:none; margin-left:10px;">Hủy</a>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>

<?php require_once '../includes/footer.php'; ?>

