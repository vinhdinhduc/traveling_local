<?php

$adminTitle = 'Chi tiết booking';
require_once dirname(dirname(__DIR__)) . '/includes/config.php';
require_once dirname(dirname(__DIR__)) . '/functions.php';
requireLogin();

$bookingId = (int)($_GET['id'] ?? 0);
if ($bookingId <= 0) {
    setFlash('error', 'Booking không hợp lệ.');
    header('Location: ' . ADMIN_URL . '/bookings/');
    exit;
}

$stmt = $pdo->prepare('SELECT b.*, h.name AS homestay_name, h.address, u.full_name, u.email, u.phone
    FROM homestay_bookings b
    JOIN homestays h ON h.id = b.homestay_id
    JOIN users u ON u.id = b.user_id
    WHERE b.id = ?
    LIMIT 1');
$stmt->execute([$bookingId]);
$booking = $stmt->fetch();

if (!$booking) {
    setFlash('error', 'Không tìm thấy booking.');
    header('Location: ' . ADMIN_URL . '/bookings/');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['review_qr_payment'])) {
    if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
        setFlash('error', 'Phiên làm việc không hợp lệ.');
        header('Location: ' . ADMIN_URL . '/bookings/detail.php?id=' . $bookingId);
        exit;
    }

    $paymentId = (int)($_POST['payment_id'] ?? 0);
    $decision = $_POST['decision'] ?? '';

    $stmtPaymentReview = $pdo->prepare('SELECT * FROM payments WHERE id = ? AND booking_id = ? AND payment_method = "BANK_QR" AND status = "pending" LIMIT 1');
    $stmtPaymentReview->execute([$paymentId, $bookingId]);
    $paymentReview = $stmtPaymentReview->fetch();

    if (!$paymentReview) {
        setFlash('error', 'Không tìm thấy minh chứng chuyển khoản cần duyệt hoặc trạng thái đã thay đổi.');
        header('Location: ' . ADMIN_URL . '/bookings/detail.php?id=' . $bookingId);
        exit;
    }

    try {
        $pdo->beginTransaction();

        if ($decision === 'approve') {
            $stmtApprovePayment = $pdo->prepare('UPDATE payments SET status = "success" WHERE id = ? AND status = "pending"');
            $stmtApprovePayment->execute([$paymentId]);

            $stmtApproveBooking = $pdo->prepare('UPDATE homestay_bookings
                SET status = "confirmed", payment_status = "paid", hold_until = NULL, updated_at = NOW()
                WHERE id = ?');
            $stmtApproveBooking->execute([$bookingId]);

            setFlash('success', 'Đã xác nhận thanh toán QR và duyệt booking thành công.');
        } elseif ($decision === 'reject') {
            $stmtRejectPayment = $pdo->prepare('UPDATE payments SET status = "failed" WHERE id = ? AND status = "pending"');
            $stmtRejectPayment->execute([$paymentId]);

            $stmtRejectBooking = $pdo->prepare('UPDATE homestay_bookings
                SET payment_status = "unpaid", updated_at = NOW()
                WHERE id = ?');
            $stmtRejectBooking->execute([$bookingId]);

            setFlash('success', 'Đã từ chối minh chứng chuyển khoản. Người dùng có thể gửi lại minh chứng mới.');
        }

        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        setFlash('error', 'Có lỗi khi duyệt thanh toán: ' . $e->getMessage());
    }

    header('Location: ' . ADMIN_URL . '/bookings/detail.php?id=' . $bookingId);
    exit;
}

$stmtPayments = $pdo->prepare('SELECT * FROM payments WHERE booking_id = ? ORDER BY created_at DESC');
$stmtPayments->execute([$bookingId]);
$payments = $stmtPayments->fetchAll();

require_once dirname(__DIR__) . '/includes/header.php';
?>

<div class="content-header">
    <h1><i class="fas fa-receipt" style="color:var(--admin-primary)"></i> Chi tiết booking #<?= (int)$booking['id'] ?></h1>
    <a href="<?= ADMIN_URL ?>/bookings/" class="btn-admin btn-back"><i class="fas fa-arrow-left"></i> Quay lại</a>
</div>

<div class="form-card">
    <h3>Thông tin booking</h3>
    <div class="admin-table" style="border:1px solid var(--admin-border);border-radius:10px;overflow:hidden">
        <table class="admin-table">
            <tbody>
                <tr>
                    <th style="width:220px">Homestay</th>
                    <td><?= sanitize($booking['homestay_name']) ?></td>
                </tr>
                <tr>
                    <th>Địa chỉ</th>
                    <td><?= sanitize($booking['address'] ?? 'N/A') ?></td>
                </tr>
                <tr>
                    <th>Khách hàng</th>
                    <td><?= sanitize($booking['full_name']) ?> (<?= sanitize($booking['email']) ?><?= !empty($booking['phone']) ? (' - ' . sanitize($booking['phone'])) : '' ?>)</td>
                </tr>
                <tr>
                    <th>Check-in / Check-out</th>
                    <td><?= sanitize($booking['check_in']) ?> / <?= sanitize($booking['check_out']) ?></td>
                </tr>
                <tr>
                    <th>Số khách</th>
                    <td><?= (int)$booking['guests'] ?> khách</td>
                </tr>
                <tr>
                    <th>Tổng tiền</th>
                    <td><?= formatPrice((float)$booking['total_price']) ?></td>
                </tr>
                <tr>
                    <th>Trạng thái booking</th>
                    <td>
                        <?php if ($booking['status'] === 'confirmed'): ?>
                            <span class="badge badge-success">Đã xác nhận</span>
                        <?php elseif ($booking['status'] === 'cancelled'): ?>
                            <span class="badge badge-danger">Đã hủy</span>
                        <?php else: ?>
                            <span class="badge badge-warning">Chờ xử lý</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th>Trạng thái thanh toán</th>
                    <td>
                        <?php if ($booking['payment_status'] === 'paid'): ?>
                            <span class="badge badge-success">Đã thanh toán</span>
                        <?php elseif ($booking['payment_status'] === 'refunded'): ?>
                            <span class="badge badge-danger">Đã hoàn tiền</span>
                        <?php else: ?>
                            <span class="badge badge-warning">Chưa thanh toán</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th>Giữ chỗ đến</th>
                    <td><?= !empty($booking['hold_until']) ? formatDateTime($booking['hold_until']) : 'N/A' ?></td>
                </tr>
                <tr>
                    <th>Ghi chú</th>
                    <td><?= nl2br(sanitize($booking['note'] ?? '')) ?></td>
                </tr>
                <tr>
                    <th>Tạo lúc</th>
                    <td><?= formatDateTime($booking['created_at']) ?></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<div class="table-wrapper" style="margin-top:20px">
    <div class="table-header">
        <h3>Lịch sử thanh toán</h3>
    </div>
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Số tiền</th>
                <th>Phương thức</th>
                <th>Trạng thái</th>
                <th>Mã giao dịch</th>
                <th>Thời gian</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($payments) > 0): ?>
                <?php foreach ($payments as $payment): ?>
                    <?php
                    $paymentMethod = (string)($payment['payment_method'] ?? '');
                    $paymentMeta = parseQrPaymentTransactionCode($payment['transaction_code'] ?? '');
                    ?>
                    <tr>
                        <td><?= (int)$payment['id'] ?></td>
                        <td><?= formatPrice((float)$payment['amount']) ?></td>
                        <td><?= sanitize($paymentMethod === 'BANK_QR' ? 'Chuyển khoản QR' : $paymentMethod) ?></td>
                        <td><?= sanitize($payment['status']) ?></td>
                        <td>
                            <?php if ($paymentMethod === 'BANK_QR'): ?>
                                <?php if (!empty($paymentMeta['reference'])): ?>
                                    <div><strong>Mã CK:</strong> <?= sanitize($paymentMeta['reference']) ?></div>
                                <?php endif; ?>
                                <?php if (!empty($paymentMeta['proof_image'])): ?>
                                    <div style="margin-top:6px">
                                        <a href="<?= getImageUrl($paymentMeta['proof_image'], 'payment-proofs') ?>" target="_blank">Xem minh chứng</a>
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <?= sanitize($payment['transaction_code'] ?? '') ?>
                            <?php endif; ?>
                        </td>
                        <td><?= formatDateTime($payment['created_at']) ?></td>
                    </tr>
                    <?php if ($paymentMethod === 'BANK_QR' && $payment['status'] === 'pending'): ?>
                        <tr>
                            <td colspan="6" style="background:#f8fafc">
                                <div style="display:flex;gap:10px;align-items:center;justify-content:space-between;flex-wrap:wrap">
                                    <div>
                                        <strong>Minh chứng chuyển khoản đang chờ duyệt.</strong>
                                        <?php if (!empty($paymentMeta['proof_image'])): ?>
                                            <a href="<?= getImageUrl($paymentMeta['proof_image'], 'payment-proofs') ?>" target="_blank" style="margin-left:8px">Mở ảnh minh chứng</a>
                                        <?php endif; ?>
                                    </div>
                                    <form method="POST" action="" style="display:flex;gap:8px;align-items:center">
                                        <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                                        <input type="hidden" name="payment_id" value="<?= (int)$payment['id'] ?>">
                                        <button type="submit" name="review_qr_payment" value="1" class="btn-admin btn-edit" onclick="this.form.decision.value='approve';return confirm('Xác nhận đã nhận chuyển khoản cho booking này?')">Duyệt thanh toán</button>
                                        <button type="submit" name="review_qr_payment" value="1" class="btn-admin btn-delete" onclick="this.form.decision.value='reject';return confirm('Từ chối minh chứng này?')">Từ chối</button>
                                        <input type="hidden" name="decision" value="approve">
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" style="text-align:center;padding:22px">Chưa có thanh toán cho booking này.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>