<?php

require_once 'includes/config.php';
require_once __DIR__ . '/functions.php';

requireUserLogin();
releaseExpiredPendingBookings($pdo);

$bookingId = (int)($_GET['booking_id'] ?? ($_POST['booking_id'] ?? 0));
if ($bookingId <= 0) {
    setFlash('error', 'Không tìm thấy booking cần thanh toán.');
    header('Location: ' . SITE_URL . '/profile.php?tab=bookings');
    exit;
}

$booking = getBookingByIdForUser($pdo, $bookingId, (int)$_SESSION['user_id']);
if (!$booking) {
    setFlash('error', 'Booking không tồn tại hoặc bạn không có quyền truy cập.');
    header('Location: ' . SITE_URL . '/profile.php?tab=bookings');
    exit;
}

$holdExpired = $booking['status'] === 'pending' && !empty($booking['hold_until']) && strtotime($booking['hold_until']) < time();

if ($holdExpired) {
    $pdo->prepare('UPDATE homestay_bookings SET status = "cancelled" WHERE id = ?')->execute([$bookingId]);
    setFlash('error', 'Booking đã hết thời gian giữ chỗ 10 phút. Vui lòng đặt lại.');
    header('Location: ' . SITE_URL . '/homestay-detail.php?id=' . (int)$booking['homestay_id']);
    exit;
}

$remainingSeconds = 0;
if (!empty($booking['hold_until'])) {
    $remainingSeconds = max(0, strtotime($booking['hold_until']) - time());
}

$pageTitle = 'Thanh toán booking';
$pageDescription = 'Thanh toán booking homestay';
require_once 'includes/header.php';
?>

<div class="breadcrumb">
    <div class="container">
        <ul class="breadcrumb-list">
            <li><a href="<?= SITE_URL ?>"><i class="fas fa-home"></i> Trang chủ</a></li>
            <li class="separator">/</li>
            <li class="current">Thanh toán</li>
        </ul>
    </div>
</div>

<section class="section" style="padding-top:35px">
    <div class="container" style="max-width:860px">
        <?= getFlash() ?>

        <div class="booking-card fade-in">
            <h1 style="margin-bottom:10px"><i class="fas fa-wallet" style="color:var(--secondary)"></i> Thanh toán booking #<?= (int)$booking['id'] ?></h1>
            <p style="color:var(--text-light)">Homestay: <strong><?= sanitize($booking['homestay_name']) ?></strong></p>
            <div class="booking-grid" style="margin-top:15px">
                <div><strong>Check-in</strong><br><?= sanitize($booking['check_in']) ?></div>
                <div><strong>Check-out</strong><br><?= sanitize($booking['check_out']) ?></div>
                <div><strong>Số khách</strong><br><?= (int)$booking['guests'] ?> khách</div>
            </div>
            <p style="margin-top:12px"><strong>Tổng tiền: <?= formatPrice((float)$booking['total_price']) ?></strong></p>

            <?php if ($remainingSeconds > 0): ?>
                <div class="map-note" style="margin:12px 0">
                    <i class="fas fa-clock"></i> Giữ chỗ còn lại: <strong id="hold-timer" data-seconds="<?= $remainingSeconds ?>">--:--</strong>
                </div>
            <?php else: ?>
                <div class="map-note" style="margin:12px 0;background:#f8fafc;border:1px solid #e2e8f0">
                    <i class="fas fa-hourglass-end"></i> Booking không còn đếm ngược giữ chỗ. Vui lòng tiếp tục vào trang quét QR để gửi minh chứng thanh toán.
                </div>
            <?php endif; ?>

            <div class="map-note" style="margin:12px 0;background:#f8fafc;border:1px solid #e2e8f0">
                <i class="fas fa-qrcode"></i> Phương thức thanh toán: <strong>Chuyển khoản ngân hàng qua mã QR</strong><br>
                Sau khi quét và chuyển khoản, bạn cần gửi ảnh minh chứng để admin xác nhận booking.
            </div>

            <a href="<?= SITE_URL ?>/process_payment.php?booking_id=<?= (int)$booking['id'] ?>" class="btn btn-primary">
                <i class="fas fa-qrcode"></i> Tiếp tục tới trang quét QR
            </a>
            <a href="<?= SITE_URL ?>/profile.php?tab=bookings" class="btn btn-outline" style="margin-left:10px">Quay lại</a>
        </div>
    </div>
</section>

<script>
    (function() {
        const el = document.getElementById('hold-timer');
        if (!el) return;
        let seconds = parseInt(el.getAttribute('data-seconds') || '0', 10);
        const shouldReloadWhenExpired = seconds > 0;

        function render() {
            const m = Math.floor(seconds / 60);
            const s = seconds % 60;
            el.textContent = String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
            if (seconds <= 0) {
                if (shouldReloadWhenExpired) {
                    window.location.reload();
                }
                return;
            }
            seconds -= 1;
        }

        render();
        setInterval(render, 1000);
    })();
</script>

<?php require_once 'includes/footer.php'; ?>

