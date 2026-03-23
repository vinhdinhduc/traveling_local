<?php

require_once 'includes/config.php';
require_once 'includes/functions.php';

if (isUserLoggedIn()) {
    header('Location: ' . SITE_URL);
    exit;
}

$errors = [];
$redirect = trim($_GET['redirect'] ?? ($_POST['redirect'] ?? ''));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
        $errors[] = 'Phiên làm việc không hợp lệ.';
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email không hợp lệ.';
        }

        if (empty($password)) {
            $errors[] = 'Vui lòng nhập mật khẩu.';
        }

        if (empty($errors)) {
            $stmt = $pdo->prepare('SELECT id, full_name, password, is_active FROM users WHERE email = ? LIMIT 1');
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && (int)$user['is_active'] === 1 && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = (int)$user['id'];
                $_SESSION['user_name'] = $user['full_name'];
                session_regenerate_id(true);

                if ($redirect !== '' && str_starts_with($redirect, SITE_URL)) {
                    header('Location: ' . $redirect);
                } else {
                    header('Location: ' . SITE_URL);
                }
                exit;
            }

            $errors[] = 'Thông tin đăng nhập không chính xác hoặc tài khoản đã bị khóa.';
        }
    }
}

$pageTitle = 'Đăng nhập';
require_once 'includes/header.php';
?>

<div class="breadcrumb">
    <div class="container">
        <ul class="breadcrumb-list">
            <li><a href="<?= SITE_URL ?>"><i class="fas fa-home"></i> Trang chủ</a></li>
            <li class="separator">/</li>
            <li class="current">Đăng nhập</li>
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

        <div class="form-card-public">
            <h1>Đăng nhập tài khoản</h1>
            <p>Đăng nhập để đặt homestay và gửi đánh giá hành trình.</p>

            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                <input type="hidden" name="redirect" value="<?= sanitize($redirect) ?>">

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-control" required value="<?= isset($email) ? sanitize($email) : '' ?>">
                </div>
                <div class="form-group">
                    <label for="password">Mật khẩu</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary">Đăng nhập</button>
            </form>

            <p>Chưa có tài khoản? <a href="<?= SITE_URL ?>/register.php">Đăng ký ngay</a></p>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>