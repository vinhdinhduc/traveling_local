<?php

$adminTitle = 'Sửa homestay';
$adminScripts = ['https://cdn.ckeditor.com/4.22.1/full/ckeditor.js'];

require_once dirname(dirname(__DIR__)) . '/includes/config.php';
require_once dirname(dirname(__DIR__)) . '/functions.php';
requireLogin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: ' . ADMIN_URL . '/homestays/');
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM homestays WHERE id = ?');
$stmt->execute([$id]);
$homestay = $stmt->fetch();
if (!$homestay) {
    setFlash('error', 'Không tìm thấy homestay.');
    header('Location: ' . ADMIN_URL . '/homestays/');
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
        $address = trim($_POST['address'] ?? '');
        $latitude = ($_POST['latitude'] ?? '') !== '' ? (float)$_POST['latitude'] : null;
        $longitude = ($_POST['longitude'] ?? '') !== '' ? (float)$_POST['longitude'] : null;
        $pricePerNight = (float)($_POST['price_per_night'] ?? 0);
        $maxGuests = max(1, (int)($_POST['max_guests'] ?? 10));
        $checkInTime = trim($_POST['check_in_time'] ?? '14:00');
        $checkOutTime = trim($_POST['check_out_time'] ?? '12:00');
        $slug = createSlug($name);

        if ($name === '') {
            $errors[] = 'Tên homestay không được để trống.';
        }

        if (!preg_match('/^([01]\d|2[0-3]):([0-5]\d)$/', $checkInTime)) {
            $errors[] = 'Giờ check-in không hợp lệ. Định dạng đúng là HH:MM.';
        }

        if (!preg_match('/^([01]\d|2[0-3]):([0-5]\d)$/', $checkOutTime)) {
            $errors[] = 'Giờ check-out không hợp lệ. Định dạng đúng là HH:MM.';
        }

        $image = $homestay['image'];
        if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
            $newImage = uploadImage($_FILES['image'], 'homestays');
            if ($newImage) {
                if (!empty($homestay['image'])) {
                    deleteImage($homestay['image'], 'homestays');
                }
                $image = $newImage;
            } else {
                $errors[] = 'Upload ảnh không hợp lệ.';
            }
        }

        if (empty($errors)) {
            $stmtUpdate = $pdo->prepare('UPDATE homestays SET name=?, slug=?, short_description=?, description=?, address=?, latitude=?, longitude=?, price_per_night=?, image=?, max_guests=?, check_in_time=?, check_out_time=? WHERE id=?');
            $stmtUpdate->execute([$name, $slug, $shortDesc, $description, $address, $latitude, $longitude, $pricePerNight, $image, $maxGuests, $checkInTime, $checkOutTime, $id]);

            $pdo->prepare('DELETE FROM homestay_amenities WHERE homestay_id = ?')->execute([$id]);
            $selectedAmenities = $_POST['amenities'] ?? [];
            if (!empty($selectedAmenities) && is_array($selectedAmenities)) {
                $stmtAmn = $pdo->prepare('INSERT INTO homestay_amenities (homestay_id, amenity_id) VALUES (?, ?)');
                foreach ($selectedAmenities as $aId) {
                    $stmtAmn->execute([$id, (int)$aId]);
                }
            }

            setFlash('success', 'Đã cập nhật homestay.');
            header('Location: ' . ADMIN_URL . '/homestays/');
            exit;
        }
    }
}

$stmtA = $pdo->query('SELECT * FROM amenities ORDER BY sort_order ASC, name ASC');
$allAmenities = $stmtA->fetchAll();

$stmtCurr = $pdo->prepare('SELECT amenity_id FROM homestay_amenities WHERE homestay_id = ?');
$stmtCurr->execute([$id]);
$currentAmnIds = $stmtCurr->fetchAll(PDO::FETCH_COLUMN);

$csrfToken = generateCsrfToken();
require_once dirname(__DIR__) . '/includes/header.php';
?>

<div class="content-header">
    <h1><i class="fas fa-edit" style="color:var(--admin-primary)"></i> Sửa: <?= sanitize($homestay['name']) ?></h1>
    <a href="<?= ADMIN_URL ?>/homestays/" class="btn-admin btn-back"><i class="fas fa-arrow-left"></i> Quay lại</a>
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
            <label for="name">Tên homestay <span class="required">*</span></label>
            <input type="text" name="name" id="name" class="form-control" required value="<?= sanitize($homestay['name']) ?>">
        </div>

        <div class="form-group">
            <label for="address">Địa chỉ</label>
            <input type="text" name="address" id="address" class="form-control" value="<?= sanitize($homestay['address'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label for="price_per_night">Giá/đêm (VND)</label>
            <input type="number" name="price_per_night" id="price_per_night" class="form-control" min="0" step="1000" value="<?= (float)$homestay['price_per_night'] ?>">
        </div>

        <div class="form-group">
            <label for="max_guests">Sức chứa tối đa (khách)</label>
            <input type="number" name="max_guests" id="max_guests" class="form-control" min="1" step="1" value="<?= isset($homestay['max_guests']) ? (int)$homestay['max_guests'] : 10 ?>">
        </div>

        <div class="form-group">
            <label for="check_in_time">Giờ check-in (HH:MM)</label>
            <input type="time" name="check_in_time" id="check_in_time" class="form-control" value="<?= sanitize($homestay['check_in_time'] ?? '14:00') ?>">
        </div>

        <div class="form-group">
            <label for="check_out_time">Giờ check-out (HH:MM)</label>
            <input type="time" name="check_out_time" id="check_out_time" class="form-control" value="<?= sanitize($homestay['check_out_time'] ?? '12:00') ?>">
        </div>

        <div class="form-group">
            <label for="short_description">Mô tả ngắn</label>
            <textarea name="short_description" id="short_description" class="form-control" rows="3"><?= sanitize($homestay['short_description'] ?? '') ?></textarea>
        </div>

        <div class="form-group">
            <label for="description">Mô tả chi tiết</label>
            <textarea name="description" id="description" class="form-control" rows="12"><?= $homestay['description'] ?? '' ?></textarea>
        </div>

        <div class="form-group">
            <label for="latitude">Latitude</label>
            <input type="number" name="latitude" id="latitude" class="form-control" step="0.0000001" value="<?= $homestay['latitude'] !== null ? (float)$homestay['latitude'] : '' ?>">
        </div>

        <div class="form-group">
            <label for="longitude">Longitude</label>
            <input type="number" name="longitude" id="longitude" class="form-control" step="0.0000001" value="<?= $homestay['longitude'] !== null ? (float)$homestay['longitude'] : '' ?>">
        </div>

        <div class="form-group">
            <label for="image">Ảnh đại diện</label>
            <input type="file" name="image" id="image" class="form-control" accept="image/*">
            <?php if (!empty($homestay['image'])): ?>
                <div class="current-image"><span>Ảnh hiện tại:</span><img src="<?= getImageUrl($homestay['image'], 'homestays') ?>" alt=""></div>
            <?php endif; ?>
        </div>

        <div class="form-group" style="margin-top: 20px;">
            <label style="font-weight: 600; display:block; margin-bottom: 8px;">Tiện nghi <small style="font-weight:normal; color:#666;">(Chọn các tiện nghi có sẵn)</small></label>
            <div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap:10px; padding:15px; border:1px solid #eee; border-radius:6px; background:#fafafa;">
                <?php foreach ($allAmenities as $amn): ?>
                    <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                        <input type="checkbox" name="amenities[]" value="<?= $amn['id'] ?>" <?= in_array($amn['id'], $currentAmnIds) ? 'checked' : '' ?> style="width:16px; height:16px;">
                        <span><i class="fa <?= sanitize($amn['icon']) ?>" style="color:#666; width:20px; text-align:center;"></i> <?= sanitize($amn['name']) ?></span>
                    </label>
                <?php endforeach; ?>
                <?php if (empty($allAmenities)): ?>
                    <p style="color:#777; font-size:0.9rem; margin:0; grid-column: 1 / -1;">Chưa có tiện nghi nào trong hệ thống. <a href="../amenities/add.php">Thêm thẻ tiện nghi mới</a></p>
                <?php endif; ?>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-admin btn-add"><i class="fas fa-save"></i> Cập nhật</button>
            <a href="<?= ADMIN_URL ?>/homestays/" class="btn-admin btn-back"><i class="fas fa-times"></i> Hủy</a>
        </div>
    </form>
</div>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
