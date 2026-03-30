<?php
require_once '../../includes/config.php';
require_once dirname(__DIR__, 2) . '/functions.php';
requireUserLogin();

$currentUser = getCurrentUser($pdo);
if ($currentUser['role'] !== 'admin') {
    setFlash('error', 'Bạn không có quyền.');
    header('Location: ' . SITE_URL);
    exit;
}

$stmt = $pdo->query('SELECT * FROM amenities ORDER BY sort_order ASC, id DESC');
$amenities = $stmt->fetchAll();

$pageTitle = 'Quản lý Tiện nghi';
require_once '../includes/header.php';
?>

<div class="content-header">
    <h1><i class="fas fa-concierge-bell" style="color:var(--admin-primary)"></i> Quản lý danh mục Tiện nghi</h1>
    <a href="add.php" class="btn-admin btn-add"><i class="fas fa-plus"></i> Thêm tiện nghi mới</a>
</div>

<div class="table-wrapper fade-in">
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Icon</th>
                <th>Tên tiện nghi</th>
                <th>Thứ tự</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
                            <?php foreach ($amenities as $a): ?>
                                <tr>
                                    <td><?= (int)$a['id'] ?></td>
                                    <td style="font-size:1.2rem; color:#555;">
                                        <i class="fa <?= sanitize($a['icon']) ?>"></i>
                                    </td>
                                    <td>
                                        <strong><?= sanitize($a['name']) ?></strong>
                                    </td>
                                    <td>
                                        <?= (int)$a['sort_order'] ?>
                                    </td>
                                    <td>
                                        <div class="action-btns">
                                            <a href="edit.php?id=<?= $a['id'] ?>" class="btn-admin btn-edit"><i class="fas fa-edit"></i> Sửa</a>
                                            <a href="delete.php?id=<?= $a['id'] ?>" class="btn-admin btn-delete" onclick="return confirm('Bạn có chắc muốn xóa tiện nghi này? Các homestay đang dùng tiện nghi này sẽ không còn hiển thị nó nữa.');"><i class="fas fa-trash"></i> Xóa</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($amenities)): ?>
                                <tr>
                                    <td colspan="5" style="text-align: center; padding: 20px;">Chưa có tiện nghi nào.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
</div>

<?php require_once '../includes/footer.php'; ?>

