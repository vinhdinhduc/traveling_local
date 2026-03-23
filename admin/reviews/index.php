<?php

$adminTitle = 'Quản lý đánh giá';
require_once dirname(__DIR__) . '/includes/header.php';

if (isset($_GET['toggle'])) {
    $toggleId = (int)$_GET['toggle'];
    $pdo->prepare('UPDATE reviews SET is_approved = 1 - is_approved WHERE id = ?')->execute([$toggleId]);
    setFlash('success', 'Đã cập nhật trạng thái đánh giá.');
    header('Location: ' . ADMIN_URL . '/reviews/');
    exit;
}

if (isset($_GET['delete'])) {
    $deleteId = (int)$_GET['delete'];
    $pdo->prepare('DELETE FROM reviews WHERE id = ?')->execute([$deleteId]);
    setFlash('success', 'Đã xóa đánh giá.');
    header('Location: ' . ADMIN_URL . '/reviews/');
    exit;
}

$currentPageNum = getCurrentPage();
$perPage = 15;
$totalReviews = countRecords($pdo, 'reviews');
$totalPages = (int)ceil($totalReviews / $perPage);
$offset = ($currentPageNum - 1) * $perPage;

$stmt = $pdo->prepare('SELECT r.*, u.full_name, p.name AS place_name FROM reviews r JOIN users u ON u.id = r.user_id JOIN places p ON p.id = r.place_id ORDER BY r.created_at DESC LIMIT :limit OFFSET :offset');
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$reviews = $stmt->fetchAll();
?>

<div class="content-header">
    <h1><i class="fas fa-star" style="color:var(--admin-secondary)"></i> Quản lý đánh giá</h1>
</div>

<div class="table-wrapper">
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Địa điểm</th>
                <th>Người đánh giá</th>
                <th>Điểm</th>
                <th>Nội dung</th>
                <th>Trạng thái</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($reviews) > 0): ?>
                <?php foreach ($reviews as $review): ?>
                    <tr>
                        <td><?= $review['id'] ?></td>
                        <td><?= sanitize($review['place_name']) ?></td>
                        <td><?= sanitize($review['full_name']) ?></td>
                        <td><strong><?= (int)$review['rating'] ?>/5</strong></td>
                        <td><?= sanitize(excerpt($review['content'] ?? '', 65)) ?></td>
                        <td>
                            <?php if ((int)$review['is_approved'] === 1): ?>
                                <span class="badge badge-success">Hiển thị</span>
                            <?php else: ?>
                                <span class="badge badge-warning">Ẩn</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="action-btns">
                                <a href="<?= ADMIN_URL ?>/reviews/?toggle=<?= $review['id'] ?>" class="btn-admin btn-edit" data-confirm="Đổi trạng thái đánh giá này?"><i class="fas fa-check"></i></a>
                                <a href="<?= ADMIN_URL ?>/reviews/?delete=<?= $review['id'] ?>" class="btn-admin btn-delete" data-confirm="Bạn có chắc muốn xóa đánh giá này?"><i class="fas fa-trash"></i></a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" style="text-align:center;padding:30px;color:#999">Chưa có đánh giá</td>
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