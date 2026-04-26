<?php

$adminTitle = 'Quản lý user';
require_once dirname(dirname(__DIR__)) . '/includes/config.php';
require_once dirname(dirname(__DIR__)) . '/functions.php';
requireLogin();

if (isset($_GET['toggle'])) {
    $toggleId = (int)$_GET['toggle'];
    $pdo->prepare('UPDATE users SET is_active = 1 - is_active WHERE id = ?')->execute([$toggleId]);
    setFlash('success', 'Đã cập nhật trạng thái user.');
    header('Location: ' . ADMIN_URL . '/users/');
    exit;
}

if (isset($_GET['delete'])) {
    $deleteId = (int)$_GET['delete'];
    $pdo->prepare('DELETE FROM users WHERE id = ?')->execute([$deleteId]);
    setFlash('success', 'Đã xóa user.');
    header('Location: ' . ADMIN_URL . '/users/');
    exit;
}

$currentPageNum = getCurrentPage();
$perPage = 15;
$totalUsers = countRecords($pdo, 'users');
$totalPages = (int)ceil($totalUsers / $perPage);
$offset = ($currentPageNum - 1) * $perPage;

$stmt = $pdo->prepare('SELECT * FROM users ORDER BY created_at DESC LIMIT :limit OFFSET :offset');
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$users = $stmt->fetchAll();

require_once dirname(__DIR__) . '/includes/header.php';
?>

<div class="content-header">
    <h1><i class="fas fa-users" style="color:var(--admin-primary)"></i> Quản lý người dùng</h1>
</div>

<div class="table-wrapper">
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Họ tên</th>
                <th>Email</th>
                <th>SĐT</th>
                <th>Ngày đăng ký</th>
                <th>Trạng thái</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($users) > 0): ?>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= $user['id'] ?></td>
                        <td><strong><?= sanitize($user['full_name']) ?></strong></td>
                        <td><?= sanitize($user['email']) ?></td>
                        <td><?= sanitize($user['phone'] ?? '') ?></td>
                        <td><?= formatDateTime($user['created_at']) ?></td>
                        <td>
                            <?php if ((int)$user['is_active'] === 1): ?>
                                <span class="badge badge-success">Đang hoạt động</span>
                            <?php else: ?>
                                <span class="badge badge-danger">Đã khóa</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="action-btns">
                                <a href="<?= ADMIN_URL ?>/users/?toggle=<?= $user['id'] ?>" class="btn-admin btn-edit" data-confirm="Cập nhật trạng thái user này?">
                                    <i class="fas fa-power-off"></i>
                                </a>
                                <a href="<?= ADMIN_URL ?>/users/?delete=<?= $user['id'] ?>" class="btn-admin btn-delete" data-confirm="Bạn có chắc muốn xóa user này?"><i class="fas fa-trash"></i></a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" style="text-align:center;padding:30px;color:#999">Chưa có user</td>
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