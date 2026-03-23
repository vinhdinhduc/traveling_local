<?php



$adminTitle = 'Sửa bài viết';
require_once dirname(__DIR__) . '/includes/header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: ' . ADMIN_URL . '/news/');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM news WHERE id = ?");
$stmt->execute([$id]);
$news = $stmt->fetch();

if (!$news) {
    setFlash('error', 'Không tìm thấy bài viết.');
    header('Location: ' . ADMIN_URL . '/news/');
    exit;
}

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

        $image = $news['image'];
        if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
            $newImage = uploadImage($_FILES['image'], 'news');
            if ($newImage) {
                if (!empty($news['image'])) {
                    deleteImage($news['image'], 'news');
                }
                $image = $newImage;
            } else {
                $errors[] = 'Lỗi upload ảnh.';
            }
        }

        if (empty($errors)) {
            $stmt = $pdo->prepare("UPDATE news SET title=?, slug=?, content=?, excerpt=?, image=? WHERE id=?");
            $stmt->execute([$title, $slug, $content, $excerpt_text, $image, $id]);

            setFlash('success', 'Cập nhật bài viết "' . $title . '" thành công!');
            header('Location: ' . ADMIN_URL . '/news/');
            exit;
        }
    }
}

$csrfToken = generateCsrfToken();
?>

<div class="content-header">
    <h1><i class="fas fa-edit" style="color:var(--admin-secondary)"></i> Sửa: <?= sanitize(excerpt($news['title'], 40)) ?></h1>
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
                value="<?= sanitize($news['title']) ?>">
        </div>

        <div class="form-group">
            <label for="excerpt">Trích đoạn</label>
            <textarea name="excerpt" id="excerpt" class="form-control" rows="3"><?= sanitize($news['excerpt'] ?? '') ?></textarea>
        </div>

        <div class="form-group">
            <label for="content">Nội dung (hỗ trợ HTML)</label>
            <textarea name="content" id="content" class="form-control" rows="12"><?= $news['content'] ?? '' ?></textarea>
        </div>

        <div class="form-group">
            <label for="image">Ảnh đại diện</label>
            <input type="file" name="image" id="image" class="form-control" accept="image/*">
            <div class="form-hint">Để trống nếu muốn giữ ảnh cũ.</div>
            <?php if (!empty($news['image'])): ?>
                <div class="current-image">
                    <span>Ảnh hiện tại:</span>
                    <img src="<?= getImageUrl($news['image'], 'news') ?>" alt="Ảnh hiện tại">
                </div>
            <?php endif; ?>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-admin btn-add">
                <i class="fas fa-save"></i> Cập nhật
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