<?php

require_once 'includes/config.php';
require_once __DIR__ . '/functions.php';

if (isUserLoggedIn()) {
    header('Location: ' . SITE_URL);
    exit;
}

$errors = [];
$token = trim($_GET['token'] ?? ($_POST['token'] ?? ''));

if ($token === '' || !preg_match('/^[a-f0-9]{64}$/', $token)) {
    $errors[] = 'Liên kết đặt lại mật khẩu đã hết hạn hoặc không chính xác.';
}

$user = null;
if (empty($errors)) {
    $tokenHash = hash('sha256', $token);
    $stmt = $pdo->prepare('SELECT id, full_name, email FROM users WHERE reset_password_token = ? AND reset_password_expires_at IS NOT NULL AND reset_password_expires_at >= NOW() AND is_active = 1 LIMIT 1');
    $stmt->execute([$tokenHash]);
    $user = $stmt->fetch();

    if (!$user) {
        $errors[] = 'Liên kết đặt lại mật khẩu đã hết hạn hoặc không chính xác.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($errors)) {
    if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
        $errors[] = 'Phiên làm việc không hợp lệ.';
    } else {
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (strlen($password) < 6) {
            $errors[] = 'Mật khẩu phải có ít nhất 6 ký tự.';
        }

        if ($password !== $confirmPassword) {
            $errors[] = 'Mật khẩu xác nhận không khớp.';
        }

        if (empty($errors)) {
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $updateStmt = $pdo->prepare('UPDATE users SET password = ?, reset_password_token = NULL, reset_password_expires_at = NULL WHERE id = ?');
            $updateStmt->execute([$hashedPassword, (int)$user['id']]);

            setFlash('success', 'Đặt lại mật khẩu thành công. Vui lòng đăng nhập bằng mật khẩu mới.');
            header('Location: ' . SITE_URL . '/login.php');
            exit;
        }
    }
}

$pageTitle = 'Đặt lại mật khẩu';
require_once 'includes/header.php';
?>

<div class="breadcrumb">
    <div class="container">
        <ul class="breadcrumb-list">
            <li><a href="<?= SITE_URL ?>"><i class="fas fa-home"></i> Trang chủ</a></li>
            <li class="separator">/</li>
            <li class="current">Đặt lại mật khẩu</li>
        </ul>
    </div>
</div>

<section class="section auth-section">
    <div class="container auth-shell">
        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <?php foreach ($errors as $err): ?>
                    <div><?= sanitize($err) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (empty($errors) && $user): ?>
            <div class="form-card-public">
                <h1>Đặt lại mật khẩu</h1>
                <p>Xin chào <?= sanitize($user['full_name']) ?>, vui lòng nhập mật khẩu mới cho tài khoản của bạn.</p>

                <form method="POST" action="">
                    <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                    <input type="hidden" name="token" value="<?= sanitize($token) ?>">

                    <div class="form-group">
                        <label for="password">Mật khẩu mới</label>
                        <input type="password" id="password" name="password" class="form-control" required minlength="6">
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Xác nhận mật khẩu mới</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" required minlength="6">
                    </div>

                    <button type="submit" class="btn btn-primary">Cập nhật mật khẩu</button>
                </form>
            </div>
        <?php else: ?>
            <div class="form-card-public">
                <h1>Liên kết không hợp lệ</h1>
                <p>Liên kết đặt lại mật khẩu đã hết hạn hoặc không tồn tại.</p>
                <a class="btn btn-primary" href="<?= SITE_URL ?>/forgot-password.php">Yêu cầu liên kết mới</a>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>