<?php

require_once 'includes/config.php';
require_once __DIR__ . '/functions.php';

if (isUserLoggedIn()) {
    header('Location: ' . SITE_URL);
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
        $errors[] = 'Phiên làm việc không hợp lệ.';
    } else {
        $fullName = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if ($fullName === '' || mb_strlen($fullName) < 2) {
            $errors[] = 'Họ tên phải có ít nhất 2 ký tự.';
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email không hợp lệ.';
        }

        if (strlen($password) < 6) {
            $errors[] = 'Mật khẩu phải có ít nhất 6 ký tự.';
        }

        if ($password !== $confirmPassword) {
            $errors[] = 'Mật khẩu xác nhận không khớp.';
        }

        $stmtCheck = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $stmtCheck->execute([$email]);
        if ($stmtCheck->fetch()) {
            $errors[] = 'Email đã được sử dụng.';
        }

        if (empty($errors)) {
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $stmtInsert = $pdo->prepare('INSERT INTO users (full_name, email, password, phone, is_active) VALUES (?, ?, ?, ?, 1)');
            $stmtInsert->execute([$fullName, $email, $hashedPassword, $phone]);

            $_SESSION['user_id'] = (int)$pdo->lastInsertId();
            $_SESSION['user_name'] = $fullName;
            session_regenerate_id(true);

            // Gửi email xác nhận đăng ký
            sendTemplateEmail($pdo, 'registration_confirm', $email, [
                'full_name' => $fullName,
            ]);

            setFlash('success', 'Đăng ký thành công. Chào mừng bạn đến với Du lịch Vân Hồ!');
            header('Location: ' . SITE_URL);
            exit;
        }
    }
}

$pageTitle = 'Đăng ký tài khoản';
require_once 'includes/header.php';
?>

<div class="breadcrumb">
    <div class="container">
        <ul class="breadcrumb-list">
            <li><a href="<?= SITE_URL ?>"><i class="fas fa-home"></i> Trang chủ</a></li>
            <li class="separator">/</li>
            <li class="current">Đăng ký</li>
        </ul>
    </div>
</div>

<section class="section auth-section">
    <div class="container auth-shell">
        <?= getFlash() ?>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <?php foreach ($errors as $err): ?>
                    <div><?= sanitize($err) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="form-card-public">
            <h1>Đăng ký tài khoản du lịch</h1>
            <p>Tạo tài khoản để đánh giá địa điểm và đặt homestay dễ dàng.</p>

            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">

                <div class="form-group">
                    <label for="full_name">Họ và tên</label>
                    <input type="text" id="full_name" name="full_name" class="form-control" required value="<?= isset($fullName) ? sanitize($fullName) : '' ?>">
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-control" required value="<?= isset($email) ? sanitize($email) : '' ?>">
                </div>
                <div class="form-group">
                    <label for="phone">Số điện thoại</label>
                    <input type="text" id="phone" name="phone" class="form-control" value="<?= isset($phone) ? sanitize($phone) : '' ?>">
                </div>
                <div class="form-group">
                    <label for="password">Mật khẩu</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Xác nhận mật khẩu</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary">Đăng ký</button>
            </form>

            <p>Đã có tài khoản? <a href="<?= SITE_URL ?>/login.php">Đăng nhập ngay</a></p>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>

