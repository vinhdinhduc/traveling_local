<?php

$adminTitle = 'Sửa món ăn';
$adminScripts = ['https://cdn.ckeditor.com/4.22.1/full/ckeditor.js'];

require_once dirname(dirname(__DIR__)) . '/includes/config.php';
require_once dirname(dirname(__DIR__)) . '/functions.php';
requireLogin();

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
        $subtitle = trim($_POST['subtitle'] ?? '');
        $origin = trim($_POST['origin'] ?? 'Vân Hồ, Sơn La');
        $ethnicity = trim($_POST['ethnicity'] ?? 'Thái · Mường');
        $spiceLevel = (int)($_POST['spice_level'] ?? 2);
        $bestSeason = trim($_POST['best_season'] ?? '');
        $ratingValue = (float)($_POST['rating_value'] ?? 5);
        $ingredients = trim($_POST['ingredients'] ?? '');
        $tasteTips = trim($_POST['taste_tips'] ?? '');
        $whereToEat = trim($_POST['where_to_eat'] ?? '');
        $slug = createSlug($name);

        if ($name === '') {
            $errors[] = 'Tên món ăn không được để trống.';
        }

        if ($spiceLevel < 0 || $spiceLevel > 5) {
            $errors[] = 'Độ cay phải nằm trong khoảng 0-5.';
        }

        if ($ratingValue < 0 || $ratingValue > 5) {
            $errors[] = 'Điểm đánh giá phải nằm trong khoảng 0-5.';
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
            $stmtUpdate = $pdo->prepare('UPDATE foods SET name=?, slug=?, short_description=?, description=?, image=?, subtitle=?, origin=?, ethnicity=?, spice_level=?, best_season=?, rating_value=?, ingredients=?, taste_tips=?, where_to_eat=? WHERE id=?');
            $stmtUpdate->execute([
                $name,
                $slug,
                $shortDesc,
                $description,
                $image,
                $subtitle,
                $origin,
                $ethnicity,
                $spiceLevel,
                $bestSeason,
                $ratingValue,
                $ingredients,
                $tasteTips,
                $whereToEat,
                $id
            ]);

            setFlash('success', 'Đã cập nhật món ăn.');
            header('Location: ' . ADMIN_URL . '/foods/');
            exit;
        }
    }
}

$csrfToken = generateCsrfToken();
require_once dirname(__DIR__) . '/includes/header.php';
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
            <label for="subtitle">Phụ đề thẻ thông tin</label>
            <input type="text" name="subtitle" id="subtitle" class="form-control" value="<?= sanitize($food['subtitle'] ?? 'Đặc sản Vân Hồ · Sơn La') ?>">
        </div>

        <div class="form-group">
            <label for="origin">Xuất xứ</label>
            <input type="text" name="origin" id="origin" class="form-control" value="<?= sanitize($food['origin'] ?? 'Vân Hồ, Sơn La') ?>">
        </div>

        <div class="form-group">
            <label for="ethnicity">Dân tộc</label>
            <input type="text" name="ethnicity" id="ethnicity" class="form-control" value="<?= sanitize($food['ethnicity'] ?? 'Thái · Mường') ?>">
        </div>

        <div class="form-group">
            <label for="spice_level">Độ cay (0-5)</label>
            <input type="number" name="spice_level" id="spice_level" class="form-control" min="0" max="5" step="1" value="<?= isset($food['spice_level']) ? (int)$food['spice_level'] : 2 ?>">
        </div>

        <div class="form-group">
            <label for="best_season">Mùa ngon nhất</label>
            <input type="text" name="best_season" id="best_season" class="form-control" value="<?= sanitize($food['best_season'] ?? 'Tháng 10 - 3') ?>">
        </div>

        <div class="form-group">
            <label for="rating_value">Điểm đánh giá (0-5)</label>
            <input type="number" name="rating_value" id="rating_value" class="form-control" min="0" max="5" step="0.1" value="<?= isset($food['rating_value']) ? (float)$food['rating_value'] : 5 ?>">
        </div>

        <div class="form-group">
            <label for="ingredients">Nguyên liệu đặc trưng (mỗi dòng 1 mục)</label>
            <textarea name="ingredients" id="ingredients" class="form-control" rows="6"><?= sanitize($food['ingredients'] ?? "Nguyên liệu tươi từ núi rừng Tây Bắc\nGia vị truyền thống của người Thái\nLá rừng đặc trưng vùng Vân Hồ\nRau sạch vùng cao không thuốc trừ sâu\nThịt gia súc nuôi thả tự nhiên\nHạt tiêu rừng Sơn La") ?></textarea>
        </div>

        <div class="form-group">
            <label for="taste_tips">Mẹo thưởng thức (mỗi dòng 1 mục)</label>
            <textarea name="taste_tips" id="taste_tips" class="form-control" rows="6"><?= sanitize($food['taste_tips'] ?? "Thưởng thức vào buổi sáng sớm khi sương mù còn phủ kín núi để cảm nhận trọn vị.\nKết hợp cùng rượu ngô Vân Hồ hoặc nước lá rừng để cân bằng vị đậm.\nĐặt tại nhà hàng hoặc homestay địa phương để có công thức truyền thống chuẩn vị.\nĐi chợ phiên cuối tuần để mua nguyên liệu tươi và trải nghiệm văn hóa bản địa.") ?></textarea>
        </div>

        <div class="form-group">
            <label for="where_to_eat">Thưởng thức ở đâu? (mỗi dòng theo dạng: Tên địa điểm|Vị trí)</label>
            <textarea name="where_to_eat" id="where_to_eat" class="form-control" rows="4"><?= sanitize($food['where_to_eat'] ?? "Chợ phiên Vân Hồ|T7 & CN\nNhà hàng Bản Mường|TT. Vân Hồ\nHomestay Pa Co|Bản Pa Co") ?></textarea>
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
