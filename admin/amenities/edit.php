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
    setFlash('error', 'Tiện nghi không tồn tại.');
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM amenities WHERE id = ?');
$stmt->execute([$id]);
$amenity = $stmt->fetch();

if (!$amenity) {
    setFlash('error', 'Tiện nghi không tồn tại.');
    header('Location: index.php');
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
            $updateStmt = $pdo->prepare('UPDATE amenities SET name=?, icon=?, sort_order=? WHERE id=?');
            $updateStmt->execute([$name, $icon, $sortOrder, $id]);
            
            setFlash('success', 'Đã cập nhật tiện nghi thành công.');
            header('Location: index.php');
            exit;
        }
    }
}

$pageTitle = 'Sửa Tiện nghi';
require_once '../includes/header.php';
?>

<div class="content-header">
    <h1><i class="fas fa-edit" style="color:var(--admin-primary)"></i> Sửa Tiện nghi #<?= $id ?></h1>
    <a href="index.php" class="btn-admin btn-back"><i class="fas fa-arrow-left"></i> Quay lại</a>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i>
        <div><?php foreach ($errors as $e): ?><div><?= sanitize($e) ?></div><?php endforeach; ?></div>
    </div>
<?php endif; ?>

<div class="form-card fade-in" style="max-width: 600px;">
    <form method="POST" action="">
                    <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">

                    <div class="form-group" style="margin-bottom: 20px;">
                        <label style="font-weight: 600; display:block; margin-bottom: 6px;">Tên tiện nghi *</label>
                        <input type="text" name="name" class="form-control" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px;" value="<?= sanitize($_POST['name'] ?? $amenity['name']) ?>" required>
                    </div>

                    <div class="form-group" style="margin-bottom: 20px;">
                        <label style="font-weight: 600; display:block; margin-bottom: 6px;">Icon FontAwesome *</label>
                        <input type="text" name="icon" class="form-control" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px;" value="<?= sanitize($_POST['icon'] ?? $amenity['icon']) ?>" required>
                        <small style="color:#777;">Icon hiện tại: <i class="fa <?= sanitize($amenity['icon']) ?>"></i> <?= sanitize($amenity['icon']) ?></small>
                    </div>

                    <div class="form-group" style="margin-bottom: 20px;">
                        <label style="font-weight: 600; display:block; margin-bottom: 6px;">Thứ tự hiển thị</label>
                        <input type="number" name="sort_order" class="form-control" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px;" value="<?= sanitize($_POST['sort_order'] ?? $amenity['sort_order']) ?>">
                    </div>

        <div class="form-actions">
            <button type="submit" class="btn-admin btn-add"><i class="fas fa-save"></i> Cập nhật tiện nghi</button>
        </div>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>

