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
    setFlash('error', 'Template không hợp lệ.');
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM email_templates WHERE id = ?');
$stmt->execute([$id]);
$template = $stmt->fetch();

if (!$template) {
    setFlash('error', 'Template không tồn tại.');
    header('Location: index.php');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
        $errors[] = 'Phiên làm việc không hợp lệ.';
    } else {
        $subject  = trim($_POST['subject'] ?? '');
        $bodyHtml = trim($_POST['body_html'] ?? '');

        if ($subject === '') {
            $errors[] = 'Vui lòng nhập tiêu đề email.';
        }
        if ($bodyHtml === '') {
            $errors[] = 'Vui lòng nhập nội dung email.';
        }

        if (empty($errors)) {
            $updateStmt = $pdo->prepare('UPDATE email_templates SET subject=?, body_html=?, updated_at=NOW() WHERE id=?');
            $updateStmt->execute([$subject, $bodyHtml, $id]);

            setFlash('success', 'Đã cập nhật template email thành công.');
            header('Location: index.php');
            exit;
        }
    }
}

$pageTitle = 'Sửa Email Template';
require_once '../includes/header.php';
?>

<div class="content-header">
    <h1><i class="fas fa-edit" style="color:var(--admin-primary)"></i> Sửa mẫu Email: <code><?= sanitize($template['template_key']) ?></code></h1>
    <a href="index.php" class="btn-admin btn-back"><i class="fas fa-arrow-left"></i> Quay lại</a>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i>
        <div><?php foreach ($errors as $e): ?><div><?= sanitize($e) ?></div><?php endforeach; ?></div>
    </div>
<?php endif; ?>

<div class="fade-in" style="display:grid; grid-template-columns: 2fr 1fr; gap:20px;">
    <div class="form-card">
        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">

            <div class="form-group" style="margin-bottom: 20px;">
                <label style="font-weight: 600; display:block; margin-bottom: 6px;">Tiêu đề Email (Subject) *</label>
                <input type="text" name="subject" class="form-control" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px;" value="<?= sanitize($_POST['subject'] ?? $template['subject']) ?>" required>
            </div>

            <div class="form-group" style="margin-bottom: 20px;">
                <label style="font-weight: 600; display:block; margin-bottom: 6px;">Nội dung HTML *</label>
                <!-- Sử dụng raw body_html vì nó là HTML -->
                <textarea name="body_html" class="form-control" rows="15" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px; font-family:monospace; line-height:1.5; font-size:14px;" required><?= htmlspecialchars($_POST['body_html'] ?? $template['body_html']) ?></textarea>
                <small style="color:#777; display:block; margin-top:5px;">Hỗ trợ cú pháp HTML. Dùng style nội tuyến để định dạng email tốt nhất.</small>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-admin btn-add"><i class="fas fa-save"></i> Cập nhật Template</button>
            </div>
        </form>
    </div>

    <div class="form-card" style="background:#f8f9fa;">
        <h3 style="margin-top:0; border-bottom:1px solid #ddd; padding-bottom:10px; margin-bottom:15px;">Thông tin mẫu</h3>
        <p style="color:#555; margin-bottom:20px;"><strong>Mô tả:</strong> <?= sanitize($template['description']) ?></p>

        <h4 style="margin-bottom:10px; font-size:15px;">Các biến có sẵn (Copy & Paste):</h4>
        <ul style="background:#fff; padding:15px 15px 15px 35px; border-radius:6px; border:1px dashed #ccc; color:#333;">
            <?php if ($template['template_key'] === 'registration_confirm'): ?>
                <li style="margin-bottom:5px;"><code>{{customer_name}}</code> : Tên khách hàng</li>
                <li style="margin-bottom:5px;"><code>{{email}}</code> : Email</li>
                <li style="margin-bottom:5px;"><code>{{home_url}}</code> : Link trang chủ</li>
            <?php elseif ($template['template_key'] === 'booking_confirm' || $template['template_key'] === 'payment_success'): ?>
                <li style="margin-bottom:5px;"><code>{{customer_name}}</code> : Tên khách hàng</li>
                <li style="margin-bottom:5px;"><code>{{booking_id}}</code> : Mã booking</li>
                <li style="margin-bottom:5px;"><code>{{homestay_name}}</code> : Tên homestay</li>
                <li style="margin-bottom:5px;"><code>{{check_in}}</code> : Ngày Check-in</li>
                <li style="margin-bottom:5px;"><code>{{check_out}}</code> : Ngày Check-out</li>
                <li style="margin-bottom:5px;"><code>{{guests}}</code> : Số lượng khách</li>
                <li style="margin-bottom:5px;"><code>{{total_price}}</code> : Tổng tiền</li>
                <li style="margin-bottom:5px;"><code>{{payment_url}}</code> : Link thanh toán (nếu có)</li>
            <?php elseif ($template['template_key'] === 'password_reset'): ?>
                <li style="margin-bottom:5px;"><code>{{full_name}}</code> : Tên người dùng</li>
                <li style="margin-bottom:5px;"><code>{{customer_name}}</code> : Tên người dùng (alias)</li>
                <li style="margin-bottom:5px;"><code>{{email}}</code> : Email tài khoản</li>
                <li style="margin-bottom:5px;"><code>{{reset_link}}</code> : Link đặt lại mật khẩu</li>
                <li style="margin-bottom:5px;"><code>{{expires_minutes}}</code> : Thời hạn link (phút)</li>
                <li style="margin-bottom:5px;"><code>{{site_name}}</code> : Tên website</li>
                <li style="margin-bottom:5px;"><code>{{site_url}}</code> : URL website</li>
            <?php endif; ?>
        </ul>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
