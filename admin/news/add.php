<?php



$adminTitle = 'Thêm bài viết';
require_once dirname(__DIR__) . '/includes/header.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
        $errors[] = 'Phiên làm việc không hợp lệ.';
    } else {
        $title = trim($_POST['title'] ?? '');
        $excerpt_text = trim($_POST['excerpt'] ?? '');
        $content = $_POST['content'] ?? '';
        $slug = createSlug($title);

        if (empty($title)) {
            $errors[] = 'Tiêu đề không được để trống.';
        }

        // Upload ảnh
        $image = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
            $image = uploadImage($_FILES['image'], 'news');
            if ($image === false) {
                $errors[] = 'Lỗi upload ảnh. Kiểm tra định dạng và kích thước.';
                $image = '';
            }
        }

        if (empty($errors)) {
            $stmt = $pdo->prepare("INSERT INTO news (title, slug, content, excerpt, image) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$title, $slug, $content, $excerpt_text, $image]);

            setFlash('success', 'Thêm bài viết "' . $title . '" thành công!');
            header('Location: ' . ADMIN_URL . '/news/');
            exit;
        }
    }
}

$csrfToken = generateCsrfToken();
?>

<div class="content-header">
    <h1><i class="fas fa-plus-circle" style="color:var(--admin-secondary)"></i> Thêm bài viết mới</h1>
    <a href="<?= ADMIN_URL ?>/news/" class="btn-admin btn-back">
        <i class="fas fa-arrow-left"></i> Quay lại
    </a>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert-error">
        <i class="fas fa-exclamation-circle"></i>
        <div><?php foreach ($errors as $err): ?><div><?= sanitize($err) ?></div><?php endforeach; ?></div>
    </div>
<?php endif; ?>

<div class="form-card">
    <form method="POST" action="" enctype="multipart/form-data" class="admin-form">
        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

        <div class="form-group">
            <label for="title">Tiêu đề <span class="required">*</span></label>
            <input type="text" name="title" id="title" class="form-control" required
                placeholder="Nhập tiêu đề bài viết"
                value="<?= isset($title) ? sanitize($title) : '' ?>">
        </div>

        <div class="form-group">
            <label for="excerpt">Trích đoạn</label>
            <textarea name="excerpt" id="excerpt" class="form-control" rows="3"
                placeholder="Mô tả ngắn gọn hiển thị trên card danh sách"><?= isset($excerpt_text) ? sanitize($excerpt_text) : '' ?></textarea>
        </div>

        <div class="form-group">
            <label for="content">Nội dung (hỗ trợ HTML)</label>
            <textarea name="content" id="content" class="form-control" rows="12"
                placeholder="Nội dung đầy đủ bài viết. Hỗ trợ HTML."><?= isset($content) ? $content : '' ?></textarea>
        </div>

        <div class="form-group">
            <label for="image">Ảnh đại diện</label>
            <input type="file" name="image" id="image" class="form-control" accept="image/*">
            <div class="form-hint">Định dạng: JPG, PNG, GIF, WEBP. Tối đa 5MB.</div>
            <img id="image-preview" src="" alt="Preview" style="display:none;max-width:200px;margin-top:10px;border-radius:4px">
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-admin btn-add">
                <i class="fas fa-save"></i> Lưu bài viết
            </button>
            <a href="<?= ADMIN_URL ?>/news/" class="btn-admin btn-back">
                <i class="fas fa-times"></i> Hủy
            </a>
        </div>
    </form>
</div>

<!-- CKEditor 4 Richtext -->
<script src="https://cdn.ckeditor.com/4.22.1/full/ckeditor.js"></script>
<script>
    CKEDITOR.replace('content', {
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