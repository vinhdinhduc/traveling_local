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
        $title      = trim($_POST['title'] ?? '');
        $subtitle   = trim($_POST['subtitle'] ?? '');
        $buttonText = trim($_POST['button_text'] ?? '');
        $buttonUrl  = trim($_POST['button_url'] ?? '');
        $sortOrder  = (int)($_POST['sort_order'] ?? 0);
        $isActive   = isset($_POST['is_active']) ? 1 : 0;
        $imagePath  = '';

        if ($title === '') {
            $errors[] = 'Vui lòng nhập tiêu đề.';
        }

        if (empty($_FILES['image']['name'])) {
            $errors[] = 'Vui lòng chọn một ảnh slider.';
        } else {
            $imagePath = uploadImage($_FILES['image'], 'sliders');
            if (strpos($imagePath, 'Lỗi') !== false) {
                $errors[] = $imagePath;
                $imagePath = '';
            }
        }

        if (empty($errors)) {
            $stmt = $pdo->prepare('INSERT INTO sliders (title, subtitle, button_text, button_url, image, sort_order, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([$title, $subtitle, $buttonText, $buttonUrl, $imagePath, $sortOrder, $isActive]);

            setFlash('success', 'Đã thêm slide thành công.');
            header('Location: index.php');
            exit;
        }
    }
}

$pageTitle = 'Thêm Slider mới';
require_once '../includes/header.php';
?>

<div class="content-header">
    <h1><i class="fas fa-plus" style="color:var(--admin-primary)"></i> Thêm Slider mới</h1>
    <a href="index.php" class="btn-admin btn-back"><i class="fas fa-arrow-left"></i> Quay lại</a>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i>
        <div><?php foreach ($errors as $e): ?><div><?= sanitize($e) ?></div><?php endforeach; ?></div>
    </div>
<?php endif; ?>

<div class="form-card fade-in">
    <form method="POST" action="" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">

        <div class="form-group" style="margin-bottom: 20px;">
            <label style="font-weight: 600; display:block; margin-bottom: 6px;">Tiêu đề chính *</label>
            <input type="text" name="title" class="form-control" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px;" value="<?= sanitize($_POST['title'] ?? '') ?>" required>
        </div>

        <div class="form-group" style="margin-bottom: 20px;">
            <label style="font-weight: 600; display:block; margin-bottom: 6px;">Phụ đề</label>
            <input type="text" name="subtitle" class="form-control" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px;" value="<?= sanitize($_POST['subtitle'] ?? '') ?>">
            <small style="color:#777;">Hiển thị nhỏ hơn dưới tiêu đề chính</small>
        </div>

        <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
            <div class="form-group">
                <label style="font-weight: 600; display:block; margin-bottom: 6px;">Text nút bấm</label>
                <input type="text" name="button_text" class="form-control" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px;" placeholder="Ví dụ: Khám phá ngay" value="<?= sanitize($_POST['button_text'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label style="font-weight: 600; display:block; margin-bottom: 6px;">URL nút bấm</label>
                <input type="text" name="button_url" class="form-control" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px;" placeholder="Ví dụ: /places.php" value="<?= sanitize($_POST['button_url'] ?? '') ?>">
            </div>
        </div>

        <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
            <div class="form-group">
                <label style="font-weight: 600; display:block; margin-bottom: 6px;">Ảnh Background *</label>
                <input type="file" name="image" accept="image/*" class="form-control" style="width:100%; padding:8px; border:1px solid #ddd; border-radius:6px;" required>
                <small style="color:#777;">Nên dùng ảnh ngang kích thước lớn tối ưu, tỷ lệ 16:9</small>
            </div>
            <div class="form-group">
                <label style="font-weight: 600; display:block; margin-bottom: 6px;">Thứ tự hiển thị</label>
                <input type="number" name="sort_order" class="form-control" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px;" placeholder="0" value="<?= sanitize($_POST['sort_order'] ?? '0') ?>">
                <small style="color:#777;">Slider hiển thị theo thứ tự tăng dần</small>
            </div>
        </div>

        <div class="form-group" style="margin-bottom: 25px;">
            <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                <input type="checkbox" name="is_active" value="1" checked style="width:18px; height:18px;">
                <span style="font-weight: 600;">Kích hoạt (Hiển thị ngay trên trang chủ)</span>
            </label>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-admin btn-add"><i class="fas fa-save"></i> Thêm Slider</button>
        </div>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>