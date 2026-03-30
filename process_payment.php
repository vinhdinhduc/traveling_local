<?php

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/functions.php';

requireUserLogin();

releaseExpiredPendingBookings($pdo);

$bookingId = (int)($_GET['booking_id'] ?? ($_POST['booking_id'] ?? 0));
if ($bookingId <= 0) {
    setFlash('error', 'Booking không hợp lệ.');
    header('Location: ' . SITE_URL . '/profile.php?tab=bookings');
    exit;
}

$booking = getBookingByIdForUser($pdo, $bookingId, (int)$_SESSION['user_id']);
if (!$booking) {
    setFlash('error', 'Booking không tồn tại hoặc bạn không có quyền truy cập.');
    header('Location: ' . SITE_URL . '/profile.php?tab=bookings');
    exit;
}

if ($booking['status'] !== 'pending') {
    setFlash('error', 'Booking không còn ở trạng thái chờ thanh toán.');
    header('Location: ' . SITE_URL . '/profile.php?tab=bookings');
    exit;
}

$amount = (float)$booking['total_price'];
if ($amount <= 0) {
    setFlash('error', 'Số tiền booking không hợp lệ.');
    header('Location: ' . SITE_URL . '/payment.php?booking_id=' . $bookingId);
    exit;
}

$stmtLatestPayment = $pdo->prepare('SELECT * FROM payments WHERE booking_id = ? AND payment_method = "BANK_QR" ORDER BY id DESC LIMIT 1');
$stmtLatestPayment->execute([$bookingId]);
$latestPayment = $stmtLatestPayment->fetch() ?: null;
$paymentMeta = $latestPayment ? parseQrPaymentTransactionCode($latestPayment['transaction_code'] ?? '') : ['reference' => '', 'proof_image' => ''];
$hasPendingQrProof = $latestPayment && $latestPayment['status'] === 'pending';

$holdExpired = !empty($booking['hold_until']) && strtotime($booking['hold_until']) < time();
if ($holdExpired && !$hasPendingQrProof) {
    $pdo->prepare('UPDATE homestay_bookings SET status = "cancelled" WHERE id = ?')->execute([$bookingId]);
    setFlash('error', 'Booking đã hết thời gian giữ chỗ 10 phút. Vui lòng đặt lại.');
    header('Location: ' . SITE_URL . '/homestay-detail.php?id=' . (int)$booking['homestay_id']);
    exit;
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_transfer_proof'])) {
    if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
        $errors[] = 'Phiên làm việc không hợp lệ.';
    }

    $reference = trim($_POST['transfer_reference'] ?? '');
    if ($reference === '') {
        $reference = generateFakeTransactionCode('QR');
    }
    if (mb_strlen($reference) < 4) {
        $errors[] = 'Mã giao dịch/chuyển khoản cần ít nhất 4 ký tự.';
    }

    if ($hasPendingQrProof) {
        $errors[] = 'Bạn đã gửi minh chứng chuyển khoản. Vui lòng chờ admin xác nhận.';
    }

    if (!isset($_FILES['proof_image']) || (int)$_FILES['proof_image']['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'Vui lòng tải lên ảnh minh chứng chuyển khoản.';
    }

    if (empty($errors)) {
        $proofImageName = uploadImage($_FILES['proof_image'], 'payment-proofs');
        if ($proofImageName === false) {
            $errors[] = 'Ảnh minh chứng không hợp lệ. Chỉ chấp nhận JPG/PNG/GIF/WEBP dưới 5MB.';
        } else {
            $transactionCode = buildQrPaymentTransactionCode($reference, $proofImageName);

            try {
                $pdo->beginTransaction();

                $stmtInsert = $pdo->prepare('INSERT INTO payments (booking_id, amount, payment_method, status, transaction_code) VALUES (?, ?, "BANK_QR", "pending", ?)');
                $stmtInsert->execute([$bookingId, $amount, $transactionCode]);

                // Đã có minh chứng chuyển khoản nên dừng đồng hồ giữ chỗ để chờ admin duyệt.
                $stmtBooking = $pdo->prepare('UPDATE homestay_bookings SET hold_until = NULL, updated_at = NOW() WHERE id = ? AND status = "pending"');
                $stmtBooking->execute([$bookingId]);

                $pdo->commit();

                setFlash('success', 'Đã gửi minh chứng chuyển khoản. Admin sẽ xác nhận booking trong thời gian sớm nhất.');
                header('Location: ' . SITE_URL . '/process_payment.php?booking_id=' . $bookingId);
                exit;
            } catch (Throwable $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                error_log('[BANK_QR] Submit transfer proof failed: ' . $e->getMessage());
                $errors[] = 'Không thể lưu minh chứng thanh toán. Vui lòng thử lại.';
            }
        }
    }
}

$stmtLatestPayment->execute([$bookingId]);
$latestPayment = $stmtLatestPayment->fetch() ?: null;
$paymentMeta = $latestPayment ? parseQrPaymentTransactionCode($latestPayment['transaction_code'] ?? '') : ['reference' => '', 'proof_image' => ''];
$hasPendingQrProof = $latestPayment && $latestPayment['status'] === 'pending';

$transferContent = 'BOOKING ' . $bookingId;
$qrImageUrl = buildVietQrImageUrl(
    PAYMENT_QR_BANK_BIN,
    PAYMENT_QR_ACCOUNT_NO,
    $amount,
    $transferContent,
    PAYMENT_QR_ACCOUNT_NAME
);

$remainingSeconds = 0;
if (!empty($booking['hold_until'])) {
    $remainingSeconds = max(0, strtotime($booking['hold_until']) - time());
}

$pageTitle = 'Thanh toán chuyển khoản QR';
$pageDescription = 'Quét mã QR để chuyển khoản và gửi minh chứng cho admin xác nhận booking';
require_once __DIR__ . '/includes/header.php';
?>

<div class="breadcrumb">
    <div class="container">
        <ul class="breadcrumb-list">
            <li><a href="<?= SITE_URL ?>"><i class="fas fa-home"></i> Trang chủ</a></li>
            <li class="separator">/</li>
            <li><a href="<?= SITE_URL ?>/payment.php?booking_id=<?= (int)$booking['id'] ?>">Thanh toán</a></li>
            <li class="separator">/</li>
            <li class="current">Chuyển khoản QR</li>
        </ul>
    </div>
</div>

<section class="section" style="padding-top:35px">
    <div class="container" style="max-width:920px">
        <?= getFlash() ?>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <?php foreach ($errors as $err): ?>
                    <div><?= sanitize($err) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="booking-card fade-in" style="display:grid;grid-template-columns:1.1fr 1fr;gap:22px">
            <div>
                <h1 style="margin-bottom:10px"><i class="fas fa-qrcode" style="color:var(--secondary)"></i> Quét mã QR để thanh toán</h1>
                <p style="color:var(--text-light)">Booking #<?= (int)$booking['id'] ?> - <strong><?= sanitize($booking['homestay_name']) ?></strong></p>

                <div class="booking-grid" style="margin-top:15px">
                    <div><strong>Check-in</strong><br><?= sanitize($booking['check_in']) ?></div>
                    <div><strong>Check-out</strong><br><?= sanitize($booking['check_out']) ?></div>
                    <div><strong>Số khách</strong><br><?= (int)$booking['guests'] ?> khách</div>
                </div>

                <p style="margin-top:12px"><strong>Tổng tiền cần chuyển: <?= formatPrice($amount) ?></strong></p>
                <?php if ($remainingSeconds > 0): ?>
                    <div class="map-note" style="margin:12px 0">
                        <i class="fas fa-clock"></i> Giữ chỗ còn lại: <strong id="hold-timer" data-seconds="<?= $remainingSeconds ?>">--:--</strong>
                    </div>
                <?php else: ?>
                    <div class="map-note" style="margin:12px 0;background:#f8fafc;border:1px solid #e2e8f0">
                        <i class="fas fa-hourglass-half"></i> Booking đang chờ admin kiểm tra minh chứng chuyển khoản.
                    </div>
                <?php endif; ?>

                <div style="border:1px dashed #cbd5e1;border-radius:10px;padding:12px;background:#f8fafc;margin:12px 0">
                    <div><strong>Ngân hàng (BIN):</strong> <?= sanitize(PAYMENT_QR_BANK_BIN) ?></div>
                    <div><strong>Số tài khoản:</strong> <?= sanitize(PAYMENT_QR_ACCOUNT_NO) ?></div>
                    <div><strong>Chủ tài khoản:</strong> <?= sanitize(PAYMENT_QR_ACCOUNT_NAME) ?></div>
                    <div><strong>Nội dung CK:</strong> <?= sanitize($transferContent) ?></div>
                </div>

                <?php if ($hasPendingQrProof): ?>
                    <div class="alert alert-success" style="margin-top:12px">
                        Đã nhận minh chứng chuyển khoản của bạn.
                        <?php if (!empty($paymentMeta['reference'])): ?>
                            Mã tham chiếu: <strong><?= sanitize($paymentMeta['reference']) ?></strong>.
                        <?php endif; ?>
                        Vui lòng chờ admin xác nhận.
                    </div>
                <?php else: ?>
                    <form method="POST" action="" enctype="multipart/form-data" style="margin-top:14px">
                        <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">

                        <div class="form-group">
                            <label>Mã giao dịch hoặc nội dung chuyển khoản (nếu có)</label>
                            <input type="text" name="transfer_reference" class="form-control" placeholder="Ví dụ: BOOKING <?= (int)$booking['id'] ?> hoặc mã tham chiếu app ngân hàng" maxlength="100">
                        </div>

                        <div class="form-group">
                            <label>Ảnh minh chứng chuyển khoản <span style="color:#dc2626">*</span></label>
                            <input type="file" name="proof_image" class="form-control" accept="image/*" required>
                            <small style="color:#64748b">Ảnh chụp màn hình giao dịch thành công. Tối đa 5MB.</small>
                        </div>

                        <button type="submit" name="submit_transfer_proof" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> Gửi minh chứng cho admin
                        </button>
                    </form>
                <?php endif; ?>

                <a href="<?= SITE_URL ?>/profile.php?tab=bookings" class="btn btn-outline" style="margin-top:12px">Về lịch sử booking</a>
            </div>

            <div style="display:flex;align-items:flex-start;justify-content:center">
                <div style="text-align:center;border:1px solid var(--border);border-radius:12px;padding:14px;background:#fff;width:100%;max-width:340px">
                    <p style="font-weight:700;margin-bottom:10px">Mã QR chuyển khoản</p>
                    <img src="<?= sanitize($qrImageUrl) ?>" alt="QR chuyển khoản booking <?= (int)$booking['id'] ?>" style="width:100%;max-width:280px;border-radius:8px;border:1px solid #e2e8f0">

                    <?php if (!empty($paymentMeta['proof_image'])): ?>
                        <div style="margin-top:14px;text-align:left">
                            <p style="font-weight:700;margin-bottom:8px">Ảnh minh chứng đã gửi</p>
                            <img src="<?= getImageUrl($paymentMeta['proof_image'], 'payment-proofs') ?>" alt="Minh chứng chuyển khoản" style="width:100%;border-radius:8px;border:1px solid #e2e8f0">
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    (function() {
        const el = document.getElementById('hold-timer');
        if (!el) return;
        let seconds = parseInt(el.getAttribute('data-seconds') || '0', 10);

        function render() {
            const m = Math.floor(seconds / 60);
            const s = seconds % 60;
            el.textContent = String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
            if (seconds <= 0) {
                window.location.reload();
                return;
            }
            seconds -= 1;
        }

        render();
        setInterval(render, 1000);
    })();
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>


