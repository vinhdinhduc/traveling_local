<?php



$adminTitle = 'Quản lý liên hệ';
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/functions.php';
requireLogin();

// Xử lý xóa liên hệ
if (isset($_GET['delete'])) {
    $deleteId = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM contacts WHERE id = ?");
    $stmt->execute([$deleteId]);
    setFlash('success', 'Đã xóa liên hệ thành công.');
    header('Location: ' . ADMIN_URL . '/contacts.php');
    exit;
}

// Xử lý đánh dấu đã đọc
if (isset($_GET['read'])) {
    $readId = (int)$_GET['read'];
    $stmt = $pdo->prepare("UPDATE contacts SET is_read = 1 WHERE id = ?");
    $stmt->execute([$readId]);
}

// Xem chi tiết
$viewContact = null;
if (isset($_GET['view'])) {
    $viewId = (int)$_GET['view'];
    $stmtView = $pdo->prepare("SELECT * FROM contacts WHERE id = ?");
    $stmtView->execute([$viewId]);
    $viewContact = $stmtView->fetch();

    // Đánh dấu đã đọc
    if ($viewContact && !$viewContact['is_read']) {
        $pdo->prepare("UPDATE contacts SET is_read = 1 WHERE id = ?")->execute([$viewId]);
        $viewContact['is_read'] = 1;
    }
}

// Phân trang
$currentPageNum = getCurrentPage();
$perPage = 10;
$totalContacts = countRecords($pdo, 'contacts');
$totalPages = ceil($totalContacts / $perPage);
$offset = ($currentPageNum - 1) * $perPage;

$stmt = $pdo->prepare("SELECT * FROM contacts ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$contacts = $stmt->fetchAll();

require_once 'includes/header.php';
?>

<div class="content-header">
    <h1><i class="fas fa-envelope" style="color:var(--admin-primary)"></i> Quản lý liên hệ</h1>
</div>

<!-- Chi tiết liên hệ (nếu có) -->
<?php if ($viewContact): ?>
    <div class="contact-detail-card" style="margin-bottom:25px">
        <h3 style="margin-bottom:20px">
            <i class="fas fa-envelope-open" style="color:var(--admin-primary)"></i> Chi tiết liên hệ #<?= $viewContact['id'] ?>
        </h3>
        <div class="detail-row">
            <span class="detail-label"><i class="fas fa-user"></i> Họ tên:</span>
            <span><strong><?= sanitize($viewContact['name']) ?></strong></span>
        </div>
        <div class="detail-row">
            <span class="detail-label"><i class="fas fa-envelope"></i> Email:</span>
            <span><a href="mailto:<?= sanitize($viewContact['email']) ?>"><?= sanitize($viewContact['email']) ?></a></span>
        </div>
        <div class="detail-row">
            <span class="detail-label"><i class="fas fa-phone"></i> SĐT:</span>
            <span><?= sanitize($viewContact['phone'] ?? 'Không cung cấp') ?></span>
        </div>
        <div class="detail-row">
            <span class="detail-label"><i class="fas fa-calendar"></i> Ngày gửi:</span>
            <span><?= formatDateTime($viewContact['created_at']) ?></span>
        </div>
        <div class="detail-row" style="border-bottom:none">
            <span class="detail-label"><i class="fas fa-comment"></i> Nội dung:</span>
            <span><?= nl2br(sanitize($viewContact['message'])) ?></span>
        </div>
        <div style="margin-top:15px">
            <a href="<?= ADMIN_URL ?>/contacts.php" class="btn-admin btn-back">
                <i class="fas fa-arrow-left"></i> Đóng
            </a>
        </div>
    </div>
<?php endif; ?>

<!-- Bảng danh sách -->
<div class="table-wrapper">
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Họ tên</th>
                <th>Email</th>
                <th>Nội dung</th>
                <th>Ngày gửi</th>
                <th>Trạng thái</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($contacts) > 0): ?>
                <?php foreach ($contacts as $contact): ?>
                    <tr style="<?= !$contact['is_read'] ? 'background:#fff8e1' : '' ?>">
                        <td><?= $contact['id'] ?></td>
                        <td><strong><?= sanitize($contact['name']) ?></strong></td>
                        <td><?= sanitize($contact['email']) ?></td>
                        <td><?= excerpt(sanitize($contact['message']), 50) ?></td>
                        <td><?= formatDateTime($contact['created_at']) ?></td>
                        <td>
                            <?php if ($contact['is_read']): ?>
                                <span style="color:var(--admin-primary);font-size:0.85rem"><i class="fas fa-check-circle"></i> Đã đọc</span>
                            <?php else: ?>
                                <span style="color:#e74c3c;font-size:0.85rem;font-weight:600"><i class="fas fa-circle"></i> Mới</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="action-btns">
                                <a href="<?= ADMIN_URL ?>/contacts.php?view=<?= $contact['id'] ?>"
                                    class="btn-admin btn-view" title="Xem chi tiết">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="<?= ADMIN_URL ?>/contacts.php?delete=<?= $contact['id'] ?>"
                                    class="btn-admin btn-delete" title="Xóa"
                                    data-confirm="Bạn có chắc chắn muốn xóa liên hệ này?">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" style="text-align:center;padding:30px;color:#999">Chưa có liên hệ nào</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <?php if ($totalPages > 1): ?>
        <div class="admin-pagination">
            <?php if ($currentPageNum > 1): ?>
                <a href="?page=<?= $currentPageNum - 1 ?>">&laquo; Trước</a>
            <?php endif; ?>
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?page=<?= $i ?>" class="<?= $i === $currentPageNum ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
            <?php if ($currentPageNum < $totalPages): ?>
                <a href="?page=<?= $currentPageNum + 1 ?>">Tiếp &raquo;</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>