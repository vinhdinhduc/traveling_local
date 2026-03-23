<?php


$pageTitle = 'Liên hệ';
$pageDescription = 'Liên hệ với chúng tôi để biết thêm thông tin về du lịch xã Vân Hồ, Sơn La';

require_once 'includes/header.php';

$errors = [];
$success = false;

// Xử lý form gửi liên hệ
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Kiểm tra CSRF token
    if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
        $errors[] = 'Phiên làm việc không hợp lệ. Vui lòng thử lại.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $message = trim($_POST['message'] ?? '');

        // Validation
        if (mb_strlen($name) < 2) {
            $errors[] = 'Họ tên phải có ít nhất 2 ký tự.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email không hợp lệ.';
        }
        if (mb_strlen($message) < 10) {
            $errors[] = 'Nội dung phải có ít nhất 10 ký tự.';
        }

        // Nếu không có lỗi, lưu vào database
        if (empty($errors)) {
            $stmt = $pdo->prepare("INSERT INTO contacts (name, email, phone, message) VALUES (?, ?, ?, ?)");
            $stmt->execute([
                sanitize($name),
                sanitize($email),
                sanitize($phone),
                sanitize($message)
            ]);
            $success = true;

            // Reset CSRF token
            unset($_SESSION['csrf_token']);
        }
    }
}

$csrfToken = generateCsrfToken();
?>

<!-- Breadcrumb -->
<div class="breadcrumb">
    <div class="container">
        <ul class="breadcrumb-list">
            <li><a href="<?= SITE_URL ?>"><i class="fas fa-home"></i> Trang chủ</a></li>
            <li class="separator">/</li>
            <li class="current">Liên hệ</li>
        </ul>
    </div>
</div>

<!-- Page Header -->
<div class="page-header">
    <div class="container">
        <h1><i class="fas fa-envelope" style="color:var(--primary)"></i> Liên hệ với chúng tôi</h1>
        <p>Hãy gửi tin nhắn để được tư vấn về du lịch xã Vân Hồ, tỉnh Sơn La</p>
    </div>
</div>

<!-- Nội dung liên hệ -->
<section class="section" style="padding-top:20px">
    <div class="container">
        <div class="contact-grid">
            <!-- Thông tin liên hệ -->
            <div class="contact-info fade-in">
                <h3><i class="fas fa-info-circle" style="color:var(--primary)"></i> Thông tin liên hệ</h3>

                <div class="contact-item">
                    <i class="fas fa-map-marker-alt"></i>
                    <div>
                        <h4>Địa chỉ</h4>
                        <p>UBND xã Vân Hồ, tỉnh Sơn La</p>
                    </div>
                </div>

                <div class="contact-item">
                    <i class="fas fa-phone-alt"></i>
                    <div>
                        <h4>Điện thoại</h4>
                        <p>0212 365 374</p>
                    </div>
                </div>

                <div class="contact-item">
                    <i class="fas fa-envelope"></i>
                    <div>
                        <h4>Email</h4>
                        <p>dulich@vanho.sonla.gov.vn</p>
                    </div>
                </div>

                <div class="contact-item">
                    <i class="fas fa-clock"></i>
                    <div>
                        <h4>Giờ làm việc</h4>
                        <p>Thứ 2 - Thứ 6: 7:30 - 17:00<br>Thứ 7: 7:30 - 11:30</p>
                    </div>
                </div>

                <!-- Bản đồ -->
                <div class="contact-map">
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d14868.793!2d104.809!3d20.745!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMjDCsDQ0JzQyLjAiTiAxMDTCsDQ4JzMyLjQiRQ!5e0!3m2!1svi!2s!4v1"
                        width="100%" height="250" style="border:0;" allowfullscreen="" loading="lazy">
                    </iframe>
                </div>
            </div>

            <!-- Form liên hệ -->
            <div class="contact-form-wrapper fade-in">
                <h3><i class="fas fa-paper-plane" style="color:var(--primary)"></i> Gửi liên hệ</h3>

                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> Cảm ơn bạn đã liên hệ! Chúng tôi sẽ phản hồi trong thời gian sớm nhất.
                    </div>
                <?php endif; ?>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <ul style="margin-top:5px;padding-left:20px;list-style:disc">
                            <?php foreach ($errors as $error): ?>
                                <li><?= sanitize($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" id="contact-form" novalidate>
                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

                    <div class="form-group">
                        <label for="name">Họ và tên <span class="required">*</span></label>
                        <input type="text" name="name" id="name" class="form-control"
                            placeholder="Nhập họ và tên" required minlength="2"
                            value="<?= isset($name) ? sanitize($name) : '' ?>">
                    </div>

                    <div class="form-group">
                        <label for="email">Email <span class="required">*</span></label>
                        <input type="email" name="email" id="email" class="form-control"
                            placeholder="Nhập địa chỉ email" required
                            value="<?= isset($email) ? sanitize($email) : '' ?>">
                    </div>

                    <div class="form-group">
                        <label for="phone">Số điện thoại</label>
                        <input type="tel" name="phone" id="phone" class="form-control"
                            placeholder="Nhập số điện thoại (không bắt buộc)"
                            value="<?= isset($phone) ? sanitize($phone) : '' ?>">
                    </div>

                    <div class="form-group">
                        <label for="message">Nội dung <span class="required">*</span></label>
                        <textarea name="message" id="message" class="form-control" rows="5"
                            placeholder="Nhập nội dung liên hệ" required minlength="10"><?= isset($message) ? sanitize($message) : '' ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width:100%">
                        <i class="fas fa-paper-plane"></i> Gửi liên hệ
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>