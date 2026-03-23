<?php

$adminTitle = 'Quản lý đặt phòng';
require_once dirname(__DIR__) . '/includes/header.php';

if (isset($_GET['id'], $_GET['status'])) {
    $bookingId = (int)$_GET['id'];
    $status = $_GET['status'];
    $allowedStatus = ['pending', 'confirmed', 'cancelled'];
    if (in_array($status, $allowedStatus, true)) {
        $pdo->prepare('UPDATE homestay_bookings SET status = ? WHERE id = ?')->execute([$status, $bookingId]);
        setFlash('success', 'Đã cập nhật trạng thái booking.');
    }
    header('Location: ' . ADMIN_URL . '/bookings/');
    exit;
}

$currentPageNum = getCurrentPage();
$perPage = 15;
$totalBookings = countRecords($pdo, 'homestay_bookings');
$totalPages = (int)ceil($totalBookings / $perPage);
$offset = ($currentPageNum - 1) * $perPage;

$stmt = $pdo->prepare('SELECT b.*, h.name AS homestay_name, u.full_name, u.email FROM homestay_bookings b JOIN homestays h ON h.id = b.homestay_id JOIN users u ON u.id = b.user_id ORDER BY b.created_at DESC LIMIT :limit OFFSET :offset');
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$bookings = $stmt->fetchAll();
?>

<div class="content-header">
    <h1><i class="fas fa-calendar-check" style="color:var(--admin-primary)"></i> Quản lý đặt phòng homestay</h1>
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
                                <span class="badge badge-warning">Chờ xử lý</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="action-btns">
                                <a href="<?= ADMIN_URL ?>/bookings/?id=<?= $booking['id'] ?>&status=confirmed" class="btn-admin btn-edit">Duyệt</a>
                                <a href="<?= ADMIN_URL ?>/bookings/?id=<?= $booking['id'] ?>&status=cancelled" class="btn-admin btn-delete">Hủy</a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" style="text-align:center;padding:30px;color:#999">Chưa có booking</td>
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