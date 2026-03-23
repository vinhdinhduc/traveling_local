<?php

$adminTitle = 'Sửa món ăn';
$adminScripts = ['https://cdn.ckeditor.com/4.22.1/full/ckeditor.js'];
require_once dirname(__DIR__) . '/includes/header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: ' . ADMIN_URL . '/foods/');
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM foods WHERE id = ?');
$stmt->execute([$id]);
$food = $stmt->fetch();
if (!$food) {
    setFlash('error', 'Không tìm thấy món ăn.');
    header('Location: ' . ADMIN_URL . '/foods/');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
        $errors[] = 'Phiên làm việc không hợp lệ.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $shortDesc = trim($_POST['short_description'] ?? '');
        $description = $_POST['description'] ?? '';
        $slug = createSlug($name);

        if ($name === '') {
            $errors[] = 'Tên món ăn không được để trống.';
        }

        $image = $food['image'];
        if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
            $newImage = uploadImage($_FILES['image'], 'foods');
            if ($newImage) {
                if (!empty($food['image'])) {
                    deleteImage($food['image'], 'foods');
                }
                $image = $newImage;
            } else {
                $errors[] = 'Upload ảnh không hợp lệ.';
            }
        }

        if (empty($errors)) {
            $stmtUpdate = $pdo->prepare('UPDATE foods SET name=?, slug=?, short_description=?, description=?, image=? WHERE id=?');
            $stmtUpdate->execute([$name, $slug, $shortDesc, $description, $image, $id]);

            setFlash('success', 'Đã cập nhật món ăn.');
            header('Location: ' . ADMIN_URL . '/foods/');
            exit;
        }
    }
}

$csrfToken = generateCsrfToken();
?>

<div class="content-header">
    <h1><i class="fas fa-edit" style="color:var(--admin-secondary)"></i> Sửa: <?= sanitize($food['name']) ?></h1>
    <a href="<?= ADMIN_URL ?>/foods/" class="btn-admin btn-back"><i class="fas fa-arrow-left"></i> Quay lại</a>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i>
        <div><?php foreach ($errors as $err): ?><div><?= sanitize($err) ?></div><?php endforeach; ?></div>
    </div>
<?php endif; ?>

<div class="form-card">
    <form method="POST" action="" enctype="multipart/form-data" class="admin-form">
        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

        <div class="form-group">
            <label for="name">Tên món ăn <span class="required">*</span></label>
            <input type="text" name="name" id="name" class="form-control" required value="<?= sanitize($food['name']) ?>">
        </div>

        <div class="form-group">
            <label for="short_description">Mô tả ngắn</label>
            <textarea name="short_description" id="short_description" class="form-control" rows="3"><?= sanitize($food['short_description'] ?? '') ?></textarea>
        </div>

        <div class="form-group">
            <label for="description">Mô tả chi tiết</label>
            <textarea name="description" id="description" class="form-control" rows="12"><?= $food['description'] ?? '' ?></textarea>
        </div>

        <div class="form-group">
            <label for="image">Ảnh đại diện</label>
            <input type="file" name="image" id="image" class="form-control" accept="image/*">
            <?php if (!empty($food['image'])): ?>
                <div class="current-image"><span>Ảnh hiện tại:</span><img src="<?= getImageUrl($food['image'], 'foods') ?>" alt=""></div>
            <?php endif; ?>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-admin btn-add"><i class="fas fa-save"></i> Cập nhật</button>
            <a href="<?= ADMIN_URL ?>/foods/" class="btn-admin btn-back"><i class="fas fa-times"></i> Hủy</a>
        </div>
    </form>
</div>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>