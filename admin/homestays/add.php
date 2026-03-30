<?php

$adminTitle = 'Thêm homestay';
$adminScripts = ['https://cdn.ckeditor.com/4.22.1/full/ckeditor.js'];

require_once dirname(dirname(__DIR__)) . '/includes/config.php';
require_once dirname(dirname(__DIR__)) . '/functions.php';
requireLogin();

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

        $image = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
            $image = uploadImage($_FILES['image'], 'homestays');
            if ($image === false) {
                $errors[] = 'Upload ảnh không hợp lệ.';
                $image = '';
            }
        }

        if (empty($errors)) {
            $stmt = $pdo->prepare('INSERT INTO homestays (name, slug, short_description, description, address, latitude, longitude, price_per_night, image, max_guests, check_in_time, check_out_time) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([$name, $slug, $shortDesc, $description, $address, $latitude, $longitude, $pricePerNight, $image, $maxGuests, $checkInTime, $checkOutTime]);
            $homestayId = $pdo->lastInsertId();

            $selectedAmenities = $_POST['amenities'] ?? [];
            if (!empty($selectedAmenities) && is_array($selectedAmenities)) {
                $stmtAmn = $pdo->prepare('INSERT INTO homestay_amenities (homestay_id, amenity_id) VALUES (?, ?)');
                foreach ($selectedAmenities as $aId) {
                    $stmtAmn->execute([$homestayId, (int)$aId]);
                }
            }

            setFlash('success', 'Đã thêm homestay thành công.');
            header('Location: ' . ADMIN_URL . '/homestays/');
            exit;
        }
    }
}

$stmtA = $pdo->query('SELECT * FROM amenities ORDER BY sort_order ASC, name ASC');
$allAmenities = $stmtA->fetchAll();

$csrfToken = generateCsrfToken();
require_once dirname(__DIR__) . '/includes/header.php';
?>

<div class="content-header">
    <h1><i class="fas fa-plus-circle" style="color:var(--admin-primary)"></i> Thêm homestay</h1>
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
            <input type="text" name="name" id="name" class="form-control" required value="<?= isset($name) ? sanitize($name) : '' ?>">
        </div>

        <div class="form-group">
            <label for="address">Địa chỉ</label>
            <input type="text" name="address" id="address" class="form-control" value="<?= isset($address) ? sanitize($address) : '' ?>">
        </div>

        <div class="form-group">
            <label for="price_per_night">Giá/đêm (VND)</label>
            <input type="number" name="price_per_night" id="price_per_night" class="form-control" min="0" step="1000" value="<?= isset($pricePerNight) ? (float)$pricePerNight : 0 ?>">
        </div>

        <div class="form-group">
            <label for="max_guests">Sức chứa tối đa (khách)</label>
            <input type="number" name="max_guests" id="max_guests" class="form-control" min="1" step="1" value="<?= isset($maxGuests) ? (int)$maxGuests : 10 ?>">
        </div>

        <div class="form-group">
            <label for="check_in_time">Giờ check-in (HH:MM)</label>
            <input type="time" name="check_in_time" id="check_in_time" class="form-control" value="<?= isset($checkInTime) ? sanitize($checkInTime) : '14:00' ?>">
        </div>

        <div class="form-group">
            <label for="check_out_time">Giờ check-out (HH:MM)</label>
            <input type="time" name="check_out_time" id="check_out_time" class="form-control" value="<?= isset($checkOutTime) ? sanitize($checkOutTime) : '12:00' ?>">
        </div>

        <div class="form-group">
            <label for="short_description">Mô tả ngắn</label>
            <textarea name="short_description" id="short_description" class="form-control" rows="3"><?= isset($shortDesc) ? sanitize($shortDesc) : '' ?></textarea>
        </div>

        <div class="form-group">
            <label for="description">Mô tả chi tiết</label>
            <textarea name="description" id="description" class="form-control" rows="12"><?= isset($description) ? $description : '' ?></textarea>
        </div>

        <div class="form-group">
            <label for="latitude">Latitude</label>
            <input type="number" name="latitude" id="latitude" class="form-control" step="0.0000001" value="<?= isset($latitude) && $latitude !== null ? (float)$latitude : '' ?>">
        </div>

        <div class="form-group">
            <label for="longitude">Longitude</label>
            <input type="number" name="longitude" id="longitude" class="form-control" step="0.0000001" value="<?= isset($longitude) && $longitude !== null ? (float)$longitude : '' ?>">
        </div>

        <div class="form-group">
            <label for="image">Ảnh đại diện</label>
            <input type="file" name="image" id="image" class="form-control" accept="image/*">
            <img id="image-preview" src="" alt="Preview" style="display:none;max-width:200px;margin-top:10px;border-radius:4px">
        </div>

        <div class="form-group" style="margin-top: 20px;">
            <label style="font-weight: 600; display:block; margin-bottom: 8px;">Tiện nghi <small style="font-weight:normal; color:#666;">(Chọn các tiện nghi có sẵn)</small></label>
            <div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap:10px; padding:15px; border:1px solid #eee; border-radius:6px; background:#fafafa;">
                <?php foreach ($allAmenities as $amn): ?>
                    <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                        <input type="checkbox" name="amenities[]" value="<?= $amn['id'] ?>" style="width:16px; height:16px;">
                        <span><i class="fa <?= sanitize($amn['icon']) ?>" style="color:#666; width:20px; text-align:center;"></i> <?= sanitize($amn['name']) ?></span>
                    </label>
                <?php endforeach; ?>
                <?php if (empty($allAmenities)): ?>
                    <p style="color:#777; font-size:0.9rem; margin:0; grid-column: 1 / -1;">Chưa có tiện nghi nào trong hệ thống. <a href="../amenities/add.php">Thêm thẻ tiện nghi mới</a></p>
                <?php endif; ?>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-admin btn-add"><i class="fas fa-save"></i> Lưu</button>
            <a href="<?= ADMIN_URL ?>/homestays/" class="btn-admin btn-back"><i class="fas fa-times"></i> Hủy</a>
        </div>
    </form>
</div>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
