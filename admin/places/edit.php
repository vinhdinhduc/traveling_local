<?php


$adminTitle = 'Sửa địa điểm';
$adminScripts = ['https://cdn.ckeditor.com/4.22.1/full/ckeditor.js'];
require_once dirname(dirname(__DIR__)) . '/includes/config.php';
require_once dirname(dirname(__DIR__)) . '/functions.php';
requireLogin();

// Lấy ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: ' . ADMIN_URL . '/places/');
    exit;
}

// Lấy thông tin địa điểm
$stmt = $pdo->prepare("SELECT * FROM places WHERE id = ?");
$stmt->execute([$id]);
$place = $stmt->fetch();

if (!$place) {
    setFlash('error', 'Không tìm thấy địa điểm.');
    header('Location: ' . ADMIN_URL . '/places/');
    exit;
}

// Lấy ảnh gallery hiện tại
$stmtImages = $pdo->prepare("SELECT * FROM place_images WHERE place_id = ? ORDER BY sort_order ASC");
$stmtImages->execute([$id]);
$galleryImages = $stmtImages->fetchAll();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
        $errors[] = 'Phiên làm việc không hợp lệ.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $shortDesc = trim($_POST['short_description'] ?? '');
        $description = $_POST['description'] ?? '';
        $location = trim($_POST['location'] ?? '');
        $mapEmbed = trim($_POST['map_embed'] ?? '');
        $slug = createSlug($name);

        if (empty($name)) {
            $errors[] = 'Tên địa điểm không được để trống.';
        }

        // Upload ảnh mới (nếu có)
        $mainImage = $place['image'];
        if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
            $newImage = uploadImage($_FILES['image'], 'places');
            if ($newImage) {
                // Xóa ảnh cũ
                if (!empty($place['image'])) {
                    deleteImage($place['image'], 'places');
                }
                $mainImage = $newImage;
            } else {
                $errors[] = 'Lỗi upload ảnh. Kiểm tra định dạng và kích thước.';
            }
        }

        if (empty($errors)) {
            $stmt = $pdo->prepare("UPDATE places SET name=?, slug=?, short_description=?, description=?, location=?, map_embed=?, image=? WHERE id=?");
            $stmt->execute([$name, $slug, $shortDesc, $description, $location, $mapEmbed, $mainImage, $id]);

            // Upload thêm ảnh gallery (nếu có)
            if (isset($_FILES['gallery']) && !empty($_FILES['gallery']['name'][0])) {
                $galleryFiles = $_FILES['gallery'];
                // Lấy sort_order lớn nhất hiện tại
                $maxSort = $pdo->prepare("SELECT MAX(sort_order) FROM place_images WHERE place_id = ?");
                $maxSort->execute([$id]);
                $sortOrder = (int)$maxSort->fetchColumn() + 1;

                for ($i = 0; $i < count($galleryFiles['name']); $i++) {
                    $file = [
                        'name' => $galleryFiles['name'][$i],
                        'type' => $galleryFiles['type'][$i],
                        'tmp_name' => $galleryFiles['tmp_name'][$i],
                        'error' => $galleryFiles['error'][$i],
                        'size' => $galleryFiles['size'][$i]
                    ];
                    $galleryImage = uploadImage($file, 'places');
                    if ($galleryImage) {
                        $stmtG = $pdo->prepare("INSERT INTO place_images (place_id, image, sort_order) VALUES (?, ?, ?)");
                        $stmtG->execute([$id, $galleryImage, $sortOrder++]);
                    }
                }
            }

            // Xóa ảnh gallery được chọn
            if (isset($_POST['delete_gallery']) && is_array($_POST['delete_gallery'])) {
                foreach ($_POST['delete_gallery'] as $imgId) {
                    $imgStmt = $pdo->prepare("SELECT image FROM place_images WHERE id = ? AND place_id = ?");
                    $imgStmt->execute([(int)$imgId, $id]);
                    $imgData = $imgStmt->fetch();
                    if ($imgData) {
                        deleteImage($imgData['image'], 'places');
                        $delStmt = $pdo->prepare("DELETE FROM place_images WHERE id = ?");
                        $delStmt->execute([(int)$imgId]);
                    }
                }
            }

            setFlash('success', 'Cập nhật địa điểm "' . $name . '" thành công!');
            header('Location: ' . ADMIN_URL . '/places/');
            exit;
        }
    }
}

$csrfToken = generateCsrfToken();

require_once dirname(__DIR__) . '/includes/header.php';
?>

<div class="content-header">
    <h1><i class="fas fa-edit" style="color:var(--admin-primary)"></i> Sửa: <?= sanitize($place['name']) ?></h1>
    <a href="<?= ADMIN_URL ?>/places/" class="btn-admin btn-back">
        <i class="fas fa-arrow-left"></i> Quay lại
    </a>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert-error">
        <i class="fas fa-exclamation-circle"></i>
        <div>
            <?php foreach ($errors as $err): ?>
                <div><?= sanitize($err) ?></div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<div class="form-card">
    <form method="POST" action="" enctype="multipart/form-data" class="admin-form">
        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

        <div class="form-group">
            <label for="name">Tên địa điểm <span class="required">*</span></label>
            <input type="text" name="name" id="name" class="form-control" required
                value="<?= sanitize($place['name']) ?>">
        </div>

        <div class="form-group">
            <label for="location">Địa chỉ</label>
            <input type="text" name="location" id="location" class="form-control"
                value="<?= sanitize($place['location'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label for="short_description">Mô tả ngắn</label>
            <textarea name="short_description" id="short_description" class="form-control" rows="3"><?= sanitize($place['short_description'] ?? '') ?></textarea>
        </div>

        <div class="form-group">
            <label for="description">Mô tả chi tiết (hỗ trợ HTML)</label>
            <textarea name="description" id="description" class="form-control" rows="10"><?= $place['description'] ?? '' ?></textarea>
        </div>

        <div class="form-group">
            <label for="map_embed">Google Maps Embed (iframe)</label>
            <textarea name="map_embed" id="map_embed" class="form-control" rows="3"><?= sanitize($place['map_embed'] ?? '') ?></textarea>
        </div>

        <div class="form-group">
            <label for="image">Ảnh chính</label>
            <input type="file" name="image" id="image" class="form-control" accept="image/*">
            <div class="form-hint">Để trống nếu muốn giữ ảnh cũ.</div>
            <?php if (!empty($place['image'])): ?>
                <div class="current-image">
                    <span>Ảnh hiện tại:</span>
                    <img src="<?= getImageUrl($place['image'], 'places') ?>" alt="Ảnh hiện tại">
                </div>
            <?php endif; ?>
        </div>

        <!-- Gallery hiện tại -->
        <?php if (count($galleryImages) > 0): ?>
            <div class="form-group">
                <label>Ảnh gallery hiện tại</label>
                <div style="display:flex;flex-wrap:wrap;gap:10px;margin-top:8px">
                    <?php foreach ($galleryImages as $img): ?>
                        <div style="position:relative;border:1px solid #ddd;border-radius:4px;padding:5px">
                            <img src="<?= getImageUrl($img['image'], 'places') ?>"
                                style="width:100px;height:75px;object-fit:cover;border-radius:4px" alt="Gallery">
                            <label style="display:flex;align-items:center;gap:4px;margin-top:5px;font-size:0.8rem;cursor:pointer">
                                <input type="checkbox" name="delete_gallery[]" value="<?= $img['id'] ?>">
                                Xóa
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="form-hint">Tick chọn ảnh muốn xóa.</div>
            </div>
        <?php endif; ?>

        <div class="form-group">
            <label for="gallery">Thêm ảnh gallery mới</label>
            <input type="file" name="gallery[]" id="gallery" class="form-control" accept="image/*" multiple>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-admin btn-add">
                <i class="fas fa-save"></i> Cập nhật
            </button>
            <a href="<?= ADMIN_URL ?>/places/" class="btn-admin btn-back">
                <i class="fas fa-times"></i> Hủy
            </a>
        </div>
    </form>
</div>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>