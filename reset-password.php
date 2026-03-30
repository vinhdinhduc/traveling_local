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
    $errors[] = 'Lien ket dat lai mat khau khong hop le hoac da het han.';
}

$user = null;
if (empty($errors)) {
    $tokenHash = hash('sha256', $token);
    $stmt = $pdo->prepare('SELECT id, full_name, email FROM users WHERE reset_password_token = ? AND reset_password_expires_at IS NOT NULL AND reset_password_expires_at >= NOW() AND is_active = 1 LIMIT 1');
    $stmt->execute([$tokenHash]);
    $user = $stmt->fetch();

    if (!$user) {
        $errors[] = 'Lien ket dat lai mat khau khong hop le hoac da het han.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($errors)) {
    if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
        $errors[] = 'Phien lam viec khong hop le.';
    } else {
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (strlen($password) < 6) {
            $errors[] = 'Mat khau phai co it nhat 6 ky tu.';
        }

        if ($password !== $confirmPassword) {
            $errors[] = 'Mat khau xac nhan khong khop.';
        }

        if (empty($errors)) {
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $updateStmt = $pdo->prepare('UPDATE users SET password = ?, reset_password_token = NULL, reset_password_expires_at = NULL WHERE id = ?');
            $updateStmt->execute([$hashedPassword, (int)$user['id']]);

            setFlash('success', 'Dat lai mat khau thanh cong. Vui long dang nhap bang mat khau moi.');
            header('Location: ' . SITE_URL . '/login.php');
            exit;
        }
    }
}

$pageTitle = 'Dat lai mat khau';
require_once 'includes/header.php';
?>

<div class="breadcrumb">
    <div class="container">
        <ul class="breadcrumb-list">
            <li><a href="<?= SITE_URL ?>"><i class="fas fa-home"></i> Trang chu</a></li>
            <li class="separator">/</li>
            <li class="current">Dat lai mat khau</li>
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
                <h1>Dat lai mat khau</h1>
                <p>Xin chao <?= sanitize($user['full_name']) ?>, vui long nhap mat khau moi cho tai khoan cua ban.</p>

                <form method="POST" action="">
                    <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                    <input type="hidden" name="token" value="<?= sanitize($token) ?>">

                    <div class="form-group">
                        <label for="password">Mat khau moi</label>
                        <input type="password" id="password" name="password" class="form-control" required minlength="6">
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Xac nhan mat khau moi</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" required minlength="6">
                    </div>

                    <button type="submit" class="btn btn-primary">Cap nhat mat khau</button>
                </form>
            </div>
        <?php else: ?>
            <div class="form-card-public">
                <h1>Lien ket khong hop le</h1>
                <p>Lien ket dat lai mat khau da het han hoac khong ton tai.</p>
                <a class="btn btn-primary" href="<?= SITE_URL ?>/forgot-password.php">Yeu cau lien ket moi</a>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>

