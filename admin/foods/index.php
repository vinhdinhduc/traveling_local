<?php

$adminTitle = 'Quản lý ẩm thực';
require_once dirname(__DIR__) . '/includes/header.php';

$currentPageNum = getCurrentPage();
$perPage = 10;
$totalFoods = countRecords($pdo, 'foods');
$totalPages = (int)ceil($totalFoods / $perPage);
$offset = ($currentPageNum - 1) * $perPage;

$stmt = $pdo->prepare('SELECT * FROM foods ORDER BY created_at DESC LIMIT :limit OFFSET :offset');
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$foods = $stmt->fetchAll();
?>

<div class="content-header">
    <h1><i class="fas fa-utensils" style="color:var(--admin-secondary)"></i> Quản lý ẩm thực</h1>
    <a href="<?= ADMIN_URL ?>/foods/add.php" class="btn-admin btn-add"><i class="fas fa-plus"></i> Thêm món ăn</a>
</div>

<div class="table-wrapper">
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Ảnh</th>
                <th>Tên món</th>
                <th>Lượt xem</th>
                <th>Ngày tạo</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($foods) > 0): ?>
                <?php foreach ($foods as $food): ?>
                    <tr>
                        <td><?= $food['id'] ?></td>
                        <td>
                            <?php if (!empty($food['image'])): ?>
                                <img src="<?= getImageUrl($food['image'], 'foods') ?>" class="thumb" alt="">
                            <?php else: ?>
                                <div class="thumb" style="background:#f0f0f0;display:flex;align-items:center;justify-content:center;color:#ccc;width:60px;height:45px;border-radius:4px"><i class="fas fa-image"></i></div>
                            <?php endif; ?>
                        </td>
                        <td><strong><?= sanitize($food['name']) ?></strong></td>
                        <td><?= (int)$food['views'] ?></td>
                        <td><?= formatDate($food['created_at']) ?></td>
                        <td>
                            <div class="action-btns">
                                <a href="<?= SITE_URL ?>/food-detail.php?id=<?= $food['id'] ?>" class="btn-admin btn-view" target="_blank"><i class="fas fa-eye"></i></a>
                                <a href="<?= ADMIN_URL ?>/foods/edit.php?id=<?= $food['id'] ?>" class="btn-admin btn-edit"><i class="fas fa-edit"></i></a>
                                <a href="<?= ADMIN_URL ?>/foods/delete.php?id=<?= $food['id'] ?>" class="btn-admin btn-delete" data-confirm="Bạn có chắc muốn xóa món ăn này?"><i class="fas fa-trash"></i></a>
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