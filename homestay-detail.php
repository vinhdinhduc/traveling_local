<?php

require_once 'includes/config.php';
require_once 'includes/functions.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: ' . SITE_URL . '/homestays.php');
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM homestays WHERE id = ?');
$stmt->execute([$id]);
$homestay = $stmt->fetch();

if (!$homestay) {
    header('Location: ' . SITE_URL . '/404.php');
    exit;
}

incrementViews($pdo, 'homestays', $id);
$currentUser = getCurrentUser($pdo);
$bookingErrors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_booking'])) {
    if (!isUserLoggedIn()) {
        header('Location: ' . SITE_URL . '/login.php?redirect=' . urlencode(SITE_URL . '/homestay-detail.php?id=' . $id));
        exit;
    }

    if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
        $bookingErrors[] = 'Phiên làm việc không hợp lệ.';
    } else {
        $checkIn = $_POST['check_in'] ?? '';
        $checkOut = $_POST['check_out'] ?? '';
        $guests = max(1, (int)($_POST['guests'] ?? 1));
        $note = trim($_POST['note'] ?? '');

        if (empty($checkIn) || empty($checkOut)) {
            $bookingErrors[] = 'Vui lòng chọn ngày nhận/trả phòng.';
        }

        $start = strtotime($checkIn);
        $end = strtotime($checkOut);
        if ($start === false || $end === false || $end <= $start) {
            $bookingErrors[] = 'Ngày trả phòng phải sau ngày nhận phòng.';
        }

        if (empty($bookingErrors)) {
            $nights = (int)(($end - $start) / 86400);
            $totalPrice = $nights * (float)$homestay['price_per_night'];

            $stmtBooking = $pdo->prepare('INSERT INTO homestay_bookings (homestay_id, user_id, check_in, check_out, guests, total_price, note, status) VALUES (?, ?, ?, ?, ?, ?, ?, "pending")');
            $stmtBooking->execute([
                $id,
                (int)$_SESSION['user_id'],
                $checkIn,
                $checkOut,
                $guests,
                $totalPrice,
                $note
            ]);

            setFlash('success', 'Yêu cầu đặt homestay đã được gửi. Chúng tôi sẽ xác nhận sớm.');
            header('Location: ' . SITE_URL . '/homestay-detail.php?id=' . $id);
            exit;
        }
    }
}

$pageTitle = $homestay['name'];
$pageDescription = excerpt(strip_tags($homestay['short_description'] ?? $homestay['description'] ?? ''), 160);
require_once 'includes/header.php';
?>

<div class="breadcrumb">
    <div class="container">
        <ul class="breadcrumb-list">
            <li><a href="<?= SITE_URL ?>"><i class="fas fa-home"></i> Trang chủ</a></li>
            <li class="separator">/</li>
            <li><a href="<?= SITE_URL ?>/homestays.php">Homestay</a></li>
            <li class="separator">/</li>
            <li class="current"><?= sanitize($homestay['name']) ?></li>
        </ul>
    </div>
</div>

<section class="detail-section">
    <div class="container">
        <?= getFlash() ?>

        <div class="detail-header fade-in">
            <h1><?= sanitize($homestay['name']) ?></h1>
            <div class="detail-location">
                <i class="fas fa-map-marker-alt"></i>
                <span><?= sanitize($homestay['address'] ?? 'Vân Hồ, Sơn La') ?></span>
                <span style="margin-left:15px"><i class="fas fa-money-bill-wave"></i> <?= formatPrice((float)$homestay['price_per_night']) ?>/đêm</span>
            </div>
        </div>

        <div class="detail-image fade-in">
            <?php if (!empty($homestay['image'])): ?>
                <img src="<?= getImageUrl($homestay['image'], 'homestays') ?>" alt="<?= sanitize($homestay['name']) ?>" data-lightbox>
            <?php else: ?>
                <img src="https://images.unsplash.com/photo-1496417263034-38ec4f0b665a?w=1200&h=500&fit=crop" alt="<?= sanitize($homestay['name']) ?>">
            <?php endif; ?>
        </div>

        <div class="detail-content fade-in">
            <?= $homestay['description'] ?: '<p>' . sanitize($homestay['short_description'] ?? '') . '</p>' ?>
        </div>

        <div class="booking-card fade-in">
            <h2><i class="fas fa-calendar-check" style="color:var(--secondary)"></i> Đặt homestay</h2>

            <?php if (!empty($bookingErrors)): ?>
                <div class="alert alert-error">
                    <?php foreach ($bookingErrors as $err): ?>
                        <div><?= sanitize($err) ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if ($currentUser): ?>
                <form method="POST" action="" class="booking-form">
                    <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                    <div class="booking-grid">
                        <div class="form-group">
                            <label for="check_in">Ngày nhận phòng</label>
                            <input type="date" name="check_in" id="check_in" class="form-control" required min="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="form-group">
                            <label for="check_out">Ngày trả phòng</label>
                            <input type="date" name="check_out" id="check_out" class="form-control" required min="<?= date('Y-m-d', strtotime('+1 day')) ?>">
                        </div>
                        <div class="form-group">
                            <label for="guests">Số khách</label>
                            <input type="number" name="guests" id="guests" class="form-control" min="1" max="20" value="2">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="note">Ghi chú</label>
                        <textarea name="note" id="note" class="form-control" rows="4" placeholder="Yêu cầu thêm (nếu có)"></textarea>
                    </div>
                    <button type="submit" name="submit_booking" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Gửi yêu cầu đặt phòng
                    </button>
                </form>
            <?php else: ?>
                <div class="alert alert-success">
                    Bạn cần đăng nhập để đặt homestay. <a href="<?= SITE_URL ?>/login.php?redirect=<?= urlencode(SITE_URL . '/homestay-detail.php?id=' . $id) ?>">Đăng nhập ngay</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>