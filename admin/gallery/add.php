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
        $altText   = trim($_POST['alt_text'] ?? '');
        $sortOrder = (int)($_POST['sort_order'] ?? 0);
        $isActive  = isset($_POST['is_active']) ? 1 : 0;
        $imagePath = '';

        if (empty($_FILES['image']['name'])) {
            $errors[] = 'Vui lòng chọn ảnh tải lên.';
        } else {
            $imagePath = uploadImage($_FILES['image'], 'gallery');
            if (strpos($imagePath, 'Lỗi') !== false) {
                $errors[] = $imagePath;
                $imagePath = '';
            }
        }

        if (empty($errors)) {
            $stmt = $pdo->prepare('INSERT INTO gallery (image, alt_text, sort_order, is_active) VALUES (?, ?, ?, ?)');
            $stmt->execute([$imagePath, $altText, $sortOrder, $isActive]);
            
            setFlash('success', 'Đã thêm ảnh vào gallery thành công.');
            header('Location: index.php');
            exit;
        }
    }
}

$pageTitle = 'Thêm ảnh Gallery';
require_once '../includes/header.php';
?>

<div class="content-header">
    <h1><i class="fas fa-plus" style="color:var(--admin-primary)"></i> Thêm ảnh Gallery</h1>
    <a href="index.php" class="btn-admin btn-back"><i class="fas fa-arrow-left"></i> Quay lại</a>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i>
        <div><?php foreach ($errors as $e): ?><div><?= sanitize($e) ?></div><?php endforeach; ?></div>
    </div>
<?php endif; ?>

<div class="form-card fade-in" style="max-width: 600px;">
                <form method="POST" action="" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">

                    <div class="form-group" style="margin-bottom: 20px;">
                        <label style="font-weight: 600; display:block; margin-bottom: 6px;">Chọn ảnh tải lên *</label>
                        <input type="file" name="image" accept="image/*" class="form-control" style="width:100%; padding:8px; border:1px solid #ddd; border-radius:6px;" required>
                        <small style="color:#777;">Chấp nhận .jpg, .png, .webp (max 2MB)</small>
                    </div>

                    <div class="form-group" style="margin-bottom: 20px;">
                        <label style="font-weight: 600; display:block; margin-bottom: 6px;">Đoạn văn tắt (Alt text)</label>
                        <input type="text" name="alt_text" class="form-control" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px;" placeholder="Mô tả ngắn gọn về ảnh..." value="<?= sanitize($_POST['alt_text'] ?? '') ?>">
                        <small style="color:#777;">Quan trọng cho SEO và tiếp cận người dùng khiếm thị.</small>
                    </div>

                    <div class="form-group" style="margin-bottom: 20px;">
                        <label style="font-weight: 600; display:block; margin-bottom: 6px;">Thứ tự hiển thị</label>
                        <input type="number" name="sort_order" class="form-control" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px;" placeholder="0" value="<?= sanitize($_POST['sort_order'] ?? '0') ?>">
                    </div>

                    <div class="form-group" style="margin-bottom: 25px;">
                        <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                            <input type="checkbox" name="is_active" value="1" checked style="width:18px; height:18px;">
                            <span style="font-weight: 600;">Kích hoạt ảnh ngay lập tức</span>
                        </label>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-admin btn-add"><i class="fas fa-upload"></i> Tải ảnh lên</button>
                    </div>
                </form>
            </div>

<?php require_once '../includes/footer.php'; ?>

