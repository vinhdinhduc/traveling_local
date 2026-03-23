<?php

$adminTitle = 'Quản lý homestay';
require_once dirname(__DIR__) . '/includes/header.php';

$currentPageNum = getCurrentPage();
$perPage = 10;
$totalHomestays = countRecords($pdo, 'homestays');
$totalPages = (int)ceil($totalHomestays / $perPage);
$offset = ($currentPageNum - 1) * $perPage;

$stmt = $pdo->prepare('SELECT * FROM homestays ORDER BY created_at DESC LIMIT :limit OFFSET :offset');
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$homestays = $stmt->fetchAll();
?>

<div class="content-header">
    <h1><i class="fas fa-house" style="color:var(--admin-primary)"></i> Quản lý homestay</h1>
    <a href="<?= ADMIN_URL ?>/homestays/add.php" class="btn-admin btn-add"><i class="fas fa-plus"></i> Thêm homestay</a>
</div>

<div class="table-wrapper">
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Ảnh</th>
                <th>Tên homestay</th>
                <th>Giá/đêm</th>
                <th>Địa chỉ</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($homestays) > 0): ?>
                <?php foreach ($homestays as $homestay): ?>
                    <tr>
                        <td><?= $homestay['id'] ?></td>
                        <td>
                            <?php if (!empty($homestay['image'])): ?>
                                <img src="<?= getImageUrl($homestay['image'], 'homestays') ?>" class="thumb" alt="">
                            <?php else: ?>
                                <div class="thumb" style="background:#f0f0f0;display:flex;align-items:center;justify-content:center;color:#ccc;width:60px;height:45px;border-radius:4px"><i class="fas fa-image"></i></div>
                            <?php endif; ?>
                        </td>
                        <td><strong><?= sanitize($homestay['name']) ?></strong></td>
                        <td><?= formatPrice((float)$homestay['price_per_night']) ?></td>
                        <td><?= sanitize(excerpt($homestay['address'] ?? '', 40)) ?></td>
                        <td>
                            <div class="action-btns">
                                <a href="<?= SITE_URL ?>/homestay-detail.php?id=<?= $homestay['id'] ?>" class="btn-admin btn-view" target="_blank"><i class="fas fa-eye"></i></a>
                                <a href="<?= ADMIN_URL ?>/homestays/edit.php?id=<?= $homestay['id'] ?>" class="btn-admin btn-edit"><i class="fas fa-edit"></i></a>
                                <a href="<?= ADMIN_URL ?>/homestays/delete.php?id=<?= $homestay['id'] ?>" class="btn-admin btn-delete" data-confirm="Bạn có chắc muốn xóa homestay này?"><i class="fas fa-trash"></i></a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" style="text-align:center;padding:30px;color:#999">Chưa có dữ liệu</td>
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