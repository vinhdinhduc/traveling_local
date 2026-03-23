<?php

require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';

// Nếu đã đăng nhập, redirect về dashboard
if (isLoggedIn()) {
    header('Location: ' . ADMIN_URL . '/index.php');
    exit;
}

$error = '';

// Xử lý form đăng nhập
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Kiểm tra CSRF
    if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
        $error = 'Phiên làm việc không hợp lệ.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            $error = 'Vui lòng nhập đầy đủ thông tin.';
        } else {
            // Tìm admin trong database
            $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
            $stmt->execute([$username]);
            $admin = $stmt->fetch();

            if ($admin && password_verify($password, $admin['password'])) {
                // Đăng nhập thành công
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];

                // Cập nhật last_login
                $updateStmt = $pdo->prepare("UPDATE admins SET last_login = NOW() WHERE id = ?");
                $updateStmt->execute([$admin['id']]);

                // Regenerate session ID để chống session hijacking
                session_regenerate_id(true);

                header('Location: ' . ADMIN_URL . '/index.php');
                exit;
            } else {
                $error = 'Tên đăng nhập hoặc mật khẩu không đúng.';
            }
        }
    }
}

$csrfToken = generateCsrfToken();
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập Admin - Du lịch Vân Hồ</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/admin.css">
    <?php if (file_exists(dirname(__DIR__) . '/assets/css/admin-pages/login.css')): ?>
        <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/admin-pages/login.css">
    <?php endif; ?>
</head>

<body class="login-page">

    <div class="login-card">
        <div class="login-header">
            <h1><i class="fas fa-mountain"></i> Vân Hồ Admin</h1>
            <p>Đăng nhập để quản lý website</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?= sanitize($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

            <div class="form-group">
                <label for="username"><i class="fas fa-user"></i> Tên đăng nhập</label>
                <input type="text" name="username" id="username" class="form-control"
                    placeholder="Nhập tên đăng nhập" required autofocus
                    value="<?= isset($username) ? sanitize($username) : '' ?>">
            </div>

            <div class="form-group">
                <label for="password"><i class="fas fa-lock"></i> Mật khẩu</label>
                <input type="password" name="password" id="password" class="form-control"
                    placeholder="Nhập mật khẩu" required>
            </div>

            <button type="submit" class="btn-login">
                <i class="fas fa-sign-in-alt"></i> Đăng nhập
            </button>
        </form>

        <div style="text-align:center;margin-top:20px">
            <a href="<?= SITE_URL ?>" style="color:#888;font-size:0.9rem">
                <i class="fas fa-arrow-left"></i> Về trang chủ
            </a>
        </div>
    </div>

    <?php if (file_exists(dirname(__DIR__) . '/assets/js/admin-pages/login.js')): ?>
        <script src="<?= SITE_URL ?>/assets/js/admin-pages/login.js"></script>
    <?php endif; ?>

</body>

</html>