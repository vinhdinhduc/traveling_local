<?php

$adminTitle = 'Quản lý đặt phòng';
require_once dirname(__DIR__) . '/includes/header.php';

releaseExpiredPendingBookings($pdo);

if (isset($_GET['id'], $_GET['status'])) {
    $bookingId = (int)$_GET['id'];
    $status = $_GET['status'];
    $allowedStatus = ['pending', 'confirmed', 'cancelled'];
    if (in_array($status, $allowedStatus, true)) {
        try {
            $pdo->beginTransaction();

            if ($status === 'confirmed') {
                $stmtPendingQr = $pdo->prepare('SELECT id FROM payments WHERE booking_id = ? AND payment_method = "BANK_QR" AND status = "pending" ORDER BY id DESC LIMIT 1');
                $stmtPendingQr->execute([$bookingId]);
                $pendingQrPaymentId = (int)($stmtPendingQr->fetchColumn() ?: 0);

                if ($pendingQrPaymentId > 0) {
                    $pdo->prepare('UPDATE payments SET status = "success" WHERE id = ?')->execute([$pendingQrPaymentId]);
                    $pdo->prepare('UPDATE homestay_bookings SET status = "confirmed", payment_status = "paid", hold_until = NULL, updated_at = NOW() WHERE id = ?')->execute([$bookingId]);
                } else {
                    $pdo->prepare('UPDATE homestay_bookings SET status = "confirmed", payment_status = IF(payment_status = "paid", "paid", "unpaid"), hold_until = NULL, updated_at = NOW() WHERE id = ?')->execute([$bookingId]);
                }
            } elseif ($status === 'cancelled') {
                $pdo->prepare('UPDATE homestay_bookings SET status = "cancelled", hold_until = NULL, updated_at = NOW() WHERE id = ?')->execute([$bookingId]);
            } else {
                $pdo->prepare('UPDATE homestay_bookings SET status = "pending", updated_at = NOW() WHERE id = ?')->execute([$bookingId]);
            }

            $pdo->commit();
            setFlash('success', 'Đã cập nhật trạng thái booking.');
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            setFlash('error', 'Không thể cập nhật trạng thái booking.');
        }
    }
    header('Location: ' . ADMIN_URL . '/bookings/');
    exit;
}

$statusFilter = $_GET['status_filter'] ?? '';
$paymentFilter = $_GET['payment_filter'] ?? '';
$where = [];
$params = [];

if (in_array($statusFilter, ['pending', 'confirmed', 'cancelled'], true)) {
    $where[] = 'b.status = :status_filter';
    $params[':status_filter'] = $statusFilter;
}

if (in_array($paymentFilter, ['unpaid', 'paid', 'refunded'], true)) {
    $where[] = 'b.payment_status = :payment_filter';
    $params[':payment_filter'] = $paymentFilter;
}

$whereSql = count($where) > 0 ? (' WHERE ' . implode(' AND ', $where)) : '';

$currentPageNum = getCurrentPage();
$perPage = 15;
$stmtCount = $pdo->prepare('SELECT COUNT(*) FROM homestay_bookings b' . $whereSql);
foreach ($params as $k => $v) {
    $stmtCount->bindValue($k, $v);
}
$stmtCount->execute();
$totalBookings = (int)$stmtCount->fetchColumn();
$totalPages = (int)ceil($totalBookings / $perPage);
$offset = ($currentPageNum - 1) * $perPage;

$stmt = $pdo->prepare('SELECT b.*, h.name AS homestay_name, u.full_name, u.email,
    (SELECT p.payment_method FROM payments p WHERE p.booking_id = b.id ORDER BY p.id DESC LIMIT 1) AS payment_method,
    (SELECT p.status FROM payments p WHERE p.booking_id = b.id ORDER BY p.id DESC LIMIT 1) AS latest_payment_state
    FROM homestay_bookings b
    JOIN homestays h ON h.id = b.homestay_id
    JOIN users u ON u.id = b.user_id'
    . $whereSql .
    ' ORDER BY b.created_at DESC LIMIT :limit OFFSET :offset');
foreach ($params as $k => $v) {
    $stmt->bindValue($k, $v);
}
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$bookings = $stmt->fetchAll();
?>

<div class="content-header">
    <h1><i class="fas fa-calendar-check" style="color:var(--admin-primary)"></i> Quản lý đặt phòng homestay</h1>
</div>

<div class="table-wrapper" style="margin-bottom:16px;padding:16px">
    <form method="GET" action="" style="display:grid;grid-template-columns:1fr 1fr auto;gap:10px">
        <select name="status_filter" class="form-control">
            <option value="">Tất cả trạng thái booking</option>
            <option value="pending" <?= $statusFilter === 'pending' ? 'selected' : '' ?>>Chờ xử lý</option>
            <option value="confirmed" <?= $statusFilter === 'confirmed' ? 'selected' : '' ?>>Đã xác nhận</option>
            <option value="cancelled" <?= $statusFilter === 'cancelled' ? 'selected' : '' ?>>Đã hủy</option>
        </select>
        <select name="payment_filter" class="form-control">
            <option value="">Tất cả trạng thái thanh toán</option>
            <option value="unpaid" <?= $paymentFilter === 'unpaid' ? 'selected' : '' ?>>Chưa thanh toán</option>
            <option value="paid" <?= $paymentFilter === 'paid' ? 'selected' : '' ?>>Đã thanh toán</option>
            <option value="refunded" <?= $paymentFilter === 'refunded' ? 'selected' : '' ?>>Đã hoàn tiền</option>
        </select>
        <div style="display:flex;gap:8px">
            <button type="submit" class="btn-admin btn-add">Lọc</button>
            <a href="<?= ADMIN_URL ?>/bookings/" class="btn-admin btn-back">Đặt lại</a>
        </div>
    </form>
</div>

<div class="table-wrapper">
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Homestay</th>
                <th>Khách</th>
                <th>Thời gian</th>
                <th>Tổng tiền</th>
                <th>Trạng thái</th>
                <th>Thanh toán</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($bookings) > 0): ?>
                <?php foreach ($bookings as $booking): ?>
                    <tr>
                        <td><?= $booking['id'] ?></td>
                        <td><?= sanitize($booking['homestay_name']) ?></td>
                        <td>
                            <strong><?= sanitize($booking['full_name']) ?></strong><br>
                            <span style="color:#777;font-size:12px"><?= sanitize($booking['email']) ?></span>
                        </td>
                        <td>
                            <?= sanitize($booking['check_in']) ?> - <?= sanitize($booking['check_out']) ?><br>
                            <span style="color:#777;font-size:12px"><?= (int)$booking['guests'] ?> khách</span>
                        </td>
                        <td><?= formatPrice((float)$booking['total_price']) ?></td>
                        <td>
                            <?php if ($booking['status'] === 'confirmed'): ?>
                                <span class="badge badge-success">Đã xác nhận</span>
                            <?php elseif ($booking['status'] === 'cancelled'): ?>
                                <span class="badge badge-danger">Đã hủy</span>
                            <?php else: ?>
                                <span class="badge badge-warning">Chờ xử lý</span><br>
                                <?php if (!empty($booking['hold_until'])): ?>
                                    <span style="font-size:11px;color:#64748b">Giữ tới: <?= formatDateTime($booking['hold_until']) ?></span>
                                <?php endif; ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($booking['payment_status'] === 'paid'): ?>
                                <span class="badge badge-success">Đã thanh toán</span>
                            <?php elseif (($booking['payment_method'] ?? '') === 'BANK_QR' && ($booking['latest_payment_state'] ?? '') === 'pending'): ?>
                                <span class="badge badge-warning">Chờ duyệt CK QR</span>
                            <?php elseif ($booking['payment_status'] === 'refunded'): ?>
                                <span class="badge badge-danger">Đã hoàn tiền</span>
                            <?php else: ?>
                                <span class="badge badge-warning">Chưa thanh toán</span>
                            <?php endif; ?>
                            <?php if (!empty($booking['payment_method'])): ?>
                                <div style="font-size:11px;color:#64748b;margin-top:4px"><?= sanitize($booking['payment_method'] === 'BANK_QR' ? 'Chuyển khoản QR' : $booking['payment_method']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="action-btns">
                                <a href="<?= ADMIN_URL ?>/bookings/detail.php?id=<?= $booking['id'] ?>" class="btn-admin btn-view">Chi tiết</a>
                                <a href="<?= ADMIN_URL ?>/bookings/?id=<?= $booking['id'] ?>&status=confirmed" class="btn-admin btn-edit">Duyệt</a>
                                <a href="<?= ADMIN_URL ?>/bookings/?id=<?= $booking['id'] ?>&status=cancelled" class="btn-admin btn-delete">Hủy</a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8" style="text-align:center;padding:30px;color:#999">Chưa có booking</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <?php if ($totalPages > 1): ?>
        <div class="admin-pagination">
            <?php if ($currentPageNum > 1): ?><a href="?page=<?= $currentPageNum - 1 ?>">&laquo; Trước</a><?php endif; ?>
            <?php for ($i = 1; $i <= $totalPages; $i++): ?><a href="?page=<?= $i ?>" class="<?= $i === $currentPageNum ? 'active' : '' ?>"><?= $i ?></a><?php endfor; ?>
            <?php if ($currentPageNum < $totalPages): ?><a href="?page=<?= $currentPageNum + 1 ?>">Tiếp &raquo;</a><?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>