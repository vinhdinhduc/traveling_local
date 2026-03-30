<?php

$adminTitle = 'Quản lý đánh giá';
require_once dirname(__DIR__) . '/includes/header.php';

$type = $_GET['type'] ?? 'place';
if (!in_array($type, ['place', 'homestay'], true)) {
    $type = 'place';
}

if ($type === 'place' && isset($_GET['toggle'])) {
    $toggleId = (int)$_GET['toggle'];
    $pdo->prepare('UPDATE reviews SET is_approved = 1 - is_approved WHERE id = ?')->execute([$toggleId]);
    setFlash('success', 'Đã cập nhật trạng thái đánh giá.');
    header('Location: ' . ADMIN_URL . '/reviews/?type=place');
    exit;
}

if (isset($_GET['delete'])) {
    $deleteId = (int)$_GET['delete'];
    if ($type === 'homestay') {
        $pdo->prepare('DELETE FROM homestay_reviews WHERE id = ?')->execute([$deleteId]);
    } else {
        $pdo->prepare('DELETE FROM reviews WHERE id = ?')->execute([$deleteId]);
    }
    setFlash('success', 'Đã xóa đánh giá.');
    header('Location: ' . ADMIN_URL . '/reviews/?type=' . $type);
    exit;
}

$currentPageNum = getCurrentPage();
$perPage = 15;
$totalReviews = $type === 'homestay' ? countRecords($pdo, 'homestay_reviews') : countRecords($pdo, 'reviews');
$totalPages = (int)ceil($totalReviews / $perPage);
$offset = ($currentPageNum - 1) * $perPage;

$query = $type === 'homestay'
    ? 'SELECT r.*, u.full_name, h.name AS item_name, NULL AS is_approved FROM homestay_reviews r JOIN users u ON u.id = r.user_id JOIN homestays h ON h.id = r.homestay_id ORDER BY r.created_at DESC LIMIT :limit OFFSET :offset'
    : 'SELECT r.*, u.full_name, p.name AS item_name, r.is_approved FROM reviews r JOIN users u ON u.id = r.user_id JOIN places p ON p.id = r.place_id ORDER BY r.created_at DESC LIMIT :limit OFFSET :offset';

$stmt = $pdo->prepare($query);
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$reviews = $stmt->fetchAll();
?>

<div class="content-header">
    <h1><i class="fas fa-star" style="color:var(--admin-secondary)"></i> Quản lý đánh giá</h1>
</div>

<div class="table-wrapper" style="margin-bottom:16px;padding:12px 16px;display:flex;gap:8px">
    <a href="<?= ADMIN_URL ?>/reviews/?type=place" class="btn-admin <?= $type === 'place' ? 'btn-add' : 'btn-back' ?>">Đánh giá địa điểm</a>
    <a href="<?= ADMIN_URL ?>/reviews/?type=homestay" class="btn-admin <?= $type === 'homestay' ? 'btn-add' : 'btn-back' ?>">Đánh giá homestay</a>
</div>

<div class="table-wrapper">
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th><?= $type === 'homestay' ? 'Homestay' : 'Địa điểm' ?></th>
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
                        <td><?= sanitize($review['item_name']) ?></td>
                        <td><?= sanitize($review['full_name']) ?></td>
                        <td><span class="review-stars"><?= renderStars((int)$review['rating']) ?></span></td>
                        <td><?= sanitize(excerpt($review['content'] ?? '', 65)) ?></td>
                        <td>
                            <?php if ($type === 'homestay'): ?>
                                <span class="badge badge-success"><i class="fas fa-comment-dots"></i> Công khai</span>
                            <?php else: ?>
                                <?php if ((int)$review['is_approved'] === 1): ?>
                                    <span class="badge badge-success"><i class="fas fa-eye"></i> Hiển thị</span>
                                <?php else: ?>
                                    <span class="badge badge-warning"><i class="fas fa-eye-slash"></i> Ẩn</span>
                                <?php endif; ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="action-btns">
                                <?php if ($type === 'place'): ?>
                                    <a href="<?= ADMIN_URL ?>/reviews/?type=place&toggle=<?= $review['id'] ?>" class="btn-admin btn-edit" data-confirm="Đổi trạng thái đánh giá này?" title="Ẩn/Hiện">
                                        <i class="<?= (int)$review['is_approved'] === 1 ? 'fas fa-eye-slash' : 'fas fa-eye' ?>"></i>
                                    </a>
                                <?php endif; ?>
                                <a href="<?= ADMIN_URL ?>/reviews/?type=<?= $type ?>&delete=<?= $review['id'] ?>" class="btn-admin btn-delete" data-confirm="Bạn có chắc muốn xóa đánh giá này?"><i class="fas fa-trash"></i></a>
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