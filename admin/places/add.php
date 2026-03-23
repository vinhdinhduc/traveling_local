<?php

/**
 * ADMIN - THÊM ĐỊA ĐIỂM MỚI
 * Upload ảnh chính + gallery, lưu database
 */

$adminTitle = 'Thêm địa điểm';
$adminScripts = ['https://cdn.ckeditor.com/4.22.1/full/ckeditor.js'];
require_once dirname(__DIR__) . '/includes/header.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF check
    if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
        $errors[] = 'Phiên làm việc không hợp lệ.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $shortDesc = trim($_POST['short_description'] ?? '');
        $description = $_POST['description'] ?? '';
        $location = trim($_POST['location'] ?? '');
        $mapEmbed = trim($_POST['map_embed'] ?? '');
        $slug = createSlug($name);

        // Validation
        if (empty($name)) {
            $errors[] = 'Tên địa điểm không được để trống.';
        }

        // Upload ảnh chính
        $mainImage = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
            $mainImage = uploadImage($_FILES['image'], 'places');
            if ($mainImage === false) {
                $errors[] = 'Lỗi upload ảnh chính. Kiểm tra định dạng (JPG, PNG, GIF, WEBP) và kích thước (tối đa 5MB).';
                $mainImage = '';
            }
        }

        if (empty($errors)) {
            // Lưu vào database
            $stmt = $pdo->prepare("INSERT INTO places (name, slug, short_description, description, location, map_embed, image) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $slug, $shortDesc, $description, $location, $mapEmbed, $mainImage]);
            $placeId = $pdo->lastInsertId();

            // Upload gallery images
            if (isset($_FILES['gallery']) && !empty($_FILES['gallery']['name'][0])) {
                $galleryFiles = $_FILES['gallery'];
                $sortOrder = 0;
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
                        $stmtGallery = $pdo->prepare("INSERT INTO place_images (place_id, image, sort_order) VALUES (?, ?, ?)");
                        $stmtGallery->execute([$placeId, $galleryImage, $sortOrder++]);
                    }
                }
            }

            setFlash('success', 'Thêm địa điểm "' . $name . '" thành công!');
            header('Location: ' . ADMIN_URL . '/places/');
            exit;
        }
    }
}

$csrfToken = generateCsrfToken();
?>

<div class="content-header">
    <h1><i class="fas fa-plus-circle" style="color:var(--admin-primary)"></i> Thêm địa điểm mới</h1>
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
                placeholder="Nhập tên địa điểm"
                value="<?= isset($name) ? sanitize($name) : '' ?>">
        </div>

        <div class="form-group">
            <label for="location">Địa chỉ</label>
            <input type="text" name="location" id="location" class="form-control"
                placeholder="VD: Bản Hua Tạt, xã Vân Hồ, huyện Vân Hồ, tỉnh Sơn La"
                value="<?= isset($location) ? sanitize($location) : '' ?>">
        </div>

        <div class="form-group">
            <label for="short_description">Mô tả ngắn</label>
            <textarea name="short_description" id="short_description" class="form-control" rows="3"
                placeholder="Mô tả ngắn gọn về địa điểm (hiển thị trên card)"><?= isset($shortDesc) ? sanitize($shortDesc) : '' ?></textarea>
        </div>

        <div class="form-group">
            <label for="description">Mô tả chi tiết (hỗ trợ HTML)</label>
            <textarea name="description" id="description" class="form-control" rows="10"
                placeholder="Nội dung chi tiết về địa điểm. Có thể dùng HTML: <p>, <h2>, <strong>..."><?= isset($description) ? $description : '' ?></textarea>
            <div class="form-hint">Hỗ trợ HTML formatting: &lt;p&gt;, &lt;h2&gt;, &lt;strong&gt;, &lt;em&gt;, &lt;ul&gt;, &lt;li&gt;...</div>
        </div>

        <div class="form-group">
            <label for="map_embed">Google Maps Embed (iframe)</label>
            <textarea name="map_embed" id="map_embed" class="form-control" rows="3"
                placeholder="Dán mã iframe Google Maps tại đây"><?= isset($mapEmbed) ? sanitize($mapEmbed) : '' ?></textarea>
            <div class="form-hint">Mở Google Maps > Chia sẻ > Nhúng bản đồ > Sao chép HTML</div>
        </div>

        <div class="form-group">
            <label for="image">Ảnh chính</label>
            <input type="file" name="image" id="image" class="form-control" accept="image/*">
            <div class="form-hint">Định dạng: JPG, PNG, GIF, WEBP. Tối đa 5MB.</div>
            <img id="image-preview" src="" alt="Preview" style="display:none;max-width:200px;margin-top:10px;border-radius:4px">
        </div>

        <div class="form-group">
            <label for="gallery">Ảnh gallery (chọn nhiều)</label>
            <input type="file" name="gallery[]" id="gallery" class="form-control" accept="image/*" multiple>
            <div class="form-hint">Chọn nhiều ảnh cùng lúc để tạo gallery cho địa điểm.</div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-admin btn-add">
                <i class="fas fa-save"></i> Lưu địa điểm
            </button>
            <a href="<?= ADMIN_URL ?>/places/" class="btn-admin btn-back">
                <i class="fas fa-times"></i> Hủy
            </a>
        </div>
    </form>
</div>

<!-- CKEditor 4 Richtext -->
<script src="https://cdn.ckeditor.com/4.22.1/full/ckeditor.js"></script>
<script>
    CKEDITOR.replace('description', {
        language: 'vi',
        height: 480,
        removePlugins: 'elementspath',
        resize_enabled: true,
        contentsCss: '<?= SITE_URL ?>/assets/css/style.css',
        toolbar: [{
                name: 'document',
                items: ['Source', '-', 'Preview']
            },
            {
                name: 'clipboard',
                items: ['Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo']
            },
            '/',
            {
                name: 'basicstyles',
                items: ['Bold', 'Italic', 'Underline', 'Strike', '-', 'RemoveFormat']
            },
            {
                name: 'paragraph',
                items: ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock']
            },
            {
                name: 'links',
                items: ['Link', 'Unlink']
            },
            {
                name: 'insert',
                items: ['Image', 'Table', 'HorizontalRule', 'SpecialChar']
            },
            '/',
            {
                name: 'styles',
                items: ['Styles', 'Format', 'Font', 'FontSize']
            },
            {
                name: 'colors',
                items: ['TextColor', 'BGColor']
            },
            {
                name: 'tools',
                items: ['Maximize']
            }
        ]
    });
</script>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>