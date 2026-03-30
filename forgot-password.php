<?php

require_once 'includes/config.php';
require_once __DIR__ . '/functions.php';

if (isUserLoggedIn()) {
    header('Location: ' . SITE_URL);
    exit;
}

$errors = [];
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
        $errors[] = 'Phiên làm việc không hợp lệ.';
    } else {
        $email = trim($_POST['email'] ?? '');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email không hợp lệ.';
        }

        // Basic throttle by session to reduce repeated requests.
        $now = time();
        $lastReq = (int)($_SESSION['forgot_password_last_request'] ?? 0);
        if ($lastReq > 0 && ($now - $lastReq) < 30) {
            $errors[] = 'Vui lòng đợi khoảng 30 giây trước khi gửi yêu cầu mới.';
        }

        if (empty($errors)) {
            $_SESSION['forgot_password_last_request'] = $now;

            $stmt = $pdo->prepare('SELECT id, full_name, email, is_active FROM users WHERE email = ? LIMIT 1');
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && (int)$user['is_active'] === 1) {
                $rawToken = bin2hex(random_bytes(32));
                $tokenHash = hash('sha256', $rawToken);
                $expiresAt = date('Y-m-d H:i:s', time() + 3600);

                $updateStmt = $pdo->prepare('UPDATE users SET reset_password_token = ?, reset_password_expires_at = ? WHERE id = ?');
                $updateStmt->execute([$tokenHash, $expiresAt, (int)$user['id']]);

                $resetLink = SITE_URL . '/reset-password.php?token=' . urlencode($rawToken);
                $vars = [
                    'full_name' => $user['full_name'],
                    'customer_name' => $user['full_name'],
                    'email' => $user['email'],
                    'reset_link' => $resetLink,
                    'expires_minutes' => '60',
                    'site_name' => SITE_NAME,
                    'site_url' => SITE_URL,
                    'home_url' => SITE_URL,
                ];

                $sent = sendTemplateEmail($pdo, 'password_reset', $user['email'], $vars);
                if (!$sent) {
                    $subject = 'Yeu cau dat lai mat khau - ' . SITE_NAME;
                    $htmlBody = '<p>Xin chao ' . sanitize($user['full_name']) . ',</p>'
                        . '<p>Chung toi da nhan duoc yeu cau dat lai mat khau cho tai khoan cua ban.</p>'
                        . '<p>Vui long nhan vao link ben duoi de dat lai mat khau (hieu luc trong 60 phut):</p>'
                        . '<p><a href="' . sanitize($resetLink) . '">' . sanitize($resetLink) . '</a></p>'
                        . '<p>Neu ban khong thuc hien yeu cau nay, vui long bo qua email nay.</p>';
                    sendEmail($pdo, $user['email'], $subject, $htmlBody);
                }
            }

            setFlash('success', 'Neu email ton tai trong he thong, chung toi da gui huong dan dat lai mat khau. Vui long kiem tra hop thu.');
            header('Location: ' . SITE_URL . '/forgot-password.php');
            exit;
        }
    }
}

$pageTitle = 'Quen mat khau';
require_once 'includes/header.php';
?>

<div class="breadcrumb">
    <div class="container">
        <ul class="breadcrumb-list">
            <li><a href="<?= SITE_URL ?>"><i class="fas fa-home"></i> Trang chu</a></li>
            <li class="separator">/</li>
            <li class="current">Quen mat khau</li>
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
            <h1>Quen mat khau</h1>
            <p>Nhap email dang ky. He thong se gui lien ket dat lai mat khau neu tai khoan ton tai.</p>

            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-control" required value="<?= sanitize($email) ?>">
                </div>

                <button type="submit" class="btn btn-primary">Gui lien ket dat lai mat khau</button>
            </form>

            <p style="margin-top:14px"><a href="<?= SITE_URL ?>/login.php">Quay lai dang nhap</a></p>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>

