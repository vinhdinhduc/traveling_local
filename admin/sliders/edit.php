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

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    setFlash('error', 'Slider không tồn tại.');
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM sliders WHERE id = ?');
$stmt->execute([$id]);
$slider = $stmt->fetch();

if (!$slider) {
    setFlash('error', 'Slider không tồn tại.');
    header('Location: index.php');
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
        $imagePath  = $slider['image'];

        if ($title === '') {
            $errors[] = 'Vui lòng nhập tiêu đề.';
        }

        if (!empty($_FILES['image']['name'])) {
            $newImage = uploadImage($_FILES['image'], 'sliders');
            if (strpos($newImage, 'Lỗi') !== false) {
                $errors[] = $newImage;
            } else {
                // Xóa ảnh cũ nếu có (bỏ qua nếu là URL)
                if ($imagePath && strpos($imagePath, 'http') !== 0 && file_exists(dirname(dirname(__DIR__)) . $imagePath)) {
                    @unlink(dirname(dirname(__DIR__)) . $imagePath);
                }
                $imagePath = $newImage;
            }
        }

        if (empty($errors)) {
            $stmt = $pdo->prepare('UPDATE sliders SET title=?, subtitle=?, button_text=?, button_url=?, image=?, sort_order=?, is_active=?, updated_at=NOW() WHERE id=?');
            $stmt->execute([$title, $subtitle, $buttonText, $buttonUrl, $imagePath, $sortOrder, $isActive, $id]);
            
            setFlash('success', 'Đã cập nhật slide thành công.');
            header('Location: index.php');
            exit;
        }
    }
}

$pageTitle = 'Sửa Slider';
require_once '../includes/header.php';
?>

<div class="content-header">
    <h1><i class="fas fa-edit" style="color:var(--admin-primary)"></i> Sửa Slider #<?= $id ?></h1>
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
                        <input type="text" name="title" class="form-control" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px;" value="<?= sanitize($_POST['title'] ?? $slider['title']) ?>" required>
                    </div>

                    <div class="form-group" style="margin-bottom: 20px;">
                        <label style="font-weight: 600; display:block; margin-bottom: 6px;">Phụ đề</label>
                        <input type="text" name="subtitle" class="form-control" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px;" value="<?= sanitize($_POST['subtitle'] ?? $slider['subtitle']) ?>">
                    </div>

                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                        <div class="form-group">
                            <label style="font-weight: 600; display:block; margin-bottom: 6px;">Text nút bấm</label>
                            <input type="text" name="button_text" class="form-control" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px;" value="<?= sanitize($_POST['button_text'] ?? $slider['button_text']) ?>">
                        </div>
                        <div class="form-group">
                            <label style="font-weight: 600; display:block; margin-bottom: 6px;">URL nút bấm</label>
                            <input type="text" name="button_url" class="form-control" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px;" value="<?= sanitize($_POST['button_url'] ?? $slider['button_url']) ?>">
                        </div>
                    </div>

                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                        <div class="form-group">
                            <label style="font-weight: 600; display:block; margin-bottom: 6px;">Ảnh Background</label>
                            <?php if ($slider['image']): ?>
                                <div class="current-image">
                                    <span>Ảnh hiện tại:</span><img src="<?= getImageUrl($slider['image'], 'sliders') ?>" alt="Curent image">
                                </div>
                            <?php endif; ?>
                            <input type="file" name="image" accept="image/*" class="form-control" style="width:100%; padding:8px; border:1px solid #ddd; border-radius:6px;">
                            <small style="color:#777;">Để trống nếu giữ nguyên ảnh cũ</small>
                        </div>
                        <div class="form-group">
                            <label style="font-weight: 600; display:block; margin-bottom: 6px;">Thứ tự hiển thị</label>
                            <input type="number" name="sort_order" class="form-control" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px;" value="<?= sanitize($_POST['sort_order'] ?? $slider['sort_order']) ?>">
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom: 25px;">
                        <?php $isActive = isset($_POST['is_active']) ? 1 : $slider['is_active']; ?>
                        <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                            <input type="checkbox" name="is_active" value="1" <?= $isActive ? 'checked' : '' ?> style="width:18px; height:18px;">
                            <span style="font-weight: 600;">Kích hoạt (Hiển thị ngay trên trang chủ)</span>
                        </label>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-admin btn-add"><i class="fas fa-save"></i> Cập nhật Slider</button>
                    </div>
                </form>
            </div>

<?php require_once '../includes/footer.php'; ?>

