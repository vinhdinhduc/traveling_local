<?php

/**
 * ADMIN - DANH SÁCH ĐỊA ĐIỂM
 * Hiển thị, phân trang, liên kết thêm/sửa/xóa
 */

$adminTitle = 'Quản lý địa điểm';
require_once dirname(__DIR__) . '/includes/header.php';

// Phân trang
$currentPageNum = getCurrentPage();
$perPage = 10;
$totalPlaces = countRecords($pdo, 'places');
$totalPages = ceil($totalPlaces / $perPage);
$offset = ($currentPageNum - 1) * $perPage;

// Lấy danh sách địa điểm
$stmt = $pdo->prepare("SELECT * FROM places ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$places = $stmt->fetchAll();
?>

<div class="content-header">
    <h1><i class="fas fa-map-marker-alt" style="color:var(--admin-primary)"></i> Quản lý địa điểm</h1>
    <a href="<?= ADMIN_URL ?>/places/add.php" class="btn-admin btn-add">
        <i class="fas fa-plus"></i> Thêm địa điểm
    </a>
</div>

<div class="table-wrapper">
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Ảnh</th>
                <th>Tên địa điểm</th>
                <th>Địa chỉ</th>
                <th>Lượt xem</th>
                <th>Ngày tạo</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($places) > 0): ?>
                <?php foreach ($places as $place): ?>
                    <tr>
                        <td><?= $place['id'] ?></td>
                        <td>
                            <?php if (!empty($place['image'])): ?>
                                <img src="<?= getImageUrl($place['image'], 'places') ?>" class="thumb" alt="<?= sanitize($place['name']) ?>">
                            <?php else: ?>
                                <div class="thumb" style="background:#f0f0f0;display:flex;align-items:center;justify-content:center;color:#ccc;width:60px;height:45px;border-radius:4px">
                                    <i class="fas fa-image"></i>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td><strong><?= sanitize($place['name']) ?></strong></td>
                        <td><?= sanitize(excerpt($place['location'] ?? '', 40)) ?></td>
                        <td><?= $place['views'] ?></td>
                        <td><?= formatDate($place['created_at']) ?></td>
                        <td>
                            <div class="action-btns">
                                <a href="<?= SITE_URL ?>/place-detail.php?id=<?= $place['id'] ?>"
                                    class="btn-admin btn-view" target="_blank" title="Xem">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="<?= ADMIN_URL ?>/places/edit.php?id=<?= $place['id'] ?>"
                                    class="btn-admin btn-edit" title="Sửa">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="<?= ADMIN_URL ?>/places/delete.php?id=<?= $place['id'] ?>"
                                    class="btn-admin btn-delete" title="Xóa"
                                    data-confirm="Bạn có chắc chắn muốn xóa địa điểm '<?= sanitize($place['name']) ?>'?">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" style="text-align:center;padding:30px;color:#999">Chưa có địa điểm nào</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Phân trang -->
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

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>