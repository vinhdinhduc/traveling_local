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

// Bật/tắt trạng thái (Quick action)
if (isset($_GET['toggle']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare('UPDATE sliders SET is_active = NOT is_active WHERE id = ?');
    $stmt->execute([$id]);
    setFlash('success', 'Đã thay đổi trạng thái slider.');
    header('Location: index.php');
    exit;
}

$stmt = $pdo->query('SELECT * FROM sliders ORDER BY sort_order ASC, id DESC');
$sliders = $stmt->fetchAll();

$pageTitle = 'Quản lý Slider';
require_once '../includes/header.php';
?>

<div class="content-header">
    <h1><i class="fas fa-images" style="color:var(--admin-primary)"></i> Quản lý Slider</h1>
    <a href="add.php" class="btn-admin btn-add"><i class="fas fa-plus"></i> Thêm Slide mới</a>
</div>

<div class="table-wrapper fade-in">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Ảnh</th>
                <th>Tiêu đề</th>
                <th>Thứ tự</th>
                <th>Trạng thái</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
                            <?php foreach ($sliders as $s): ?>
                                <tr>
                                    <td>
                                        <img src="<?= getImageUrl($s['image'], 'sliders') ?>" alt="Slide" style="width:120px; height:60px; object-fit:cover; border-radius:4px;">
                                    </td>
                                    <td>
                                        <strong><?= sanitize($s['title']) ?></strong>
                                        <div style="font-size: 0.85rem; color:#666;"><?= sanitize($s['subtitle']) ?></div>
                                    </td>
                                    <td>
                                        <?= (int)$s['sort_order'] ?>
                                    </td>
                                    <td>
                                        <?php if ($s['is_active']): ?>
                                            <a href="?toggle=1&id=<?= $s['id'] ?>" class="badge" style="background:#cdf5d7; color:#155724; text-decoration:none; padding:4px 8px; border-radius:12px; font-size:0.8rem;">Đang hiện</a>
                                        <?php else: ?>
                                            <a href="?toggle=1&id=<?= $s['id'] ?>" class="badge" style="background:#fde8e8; color:#721c24; text-decoration:none; padding:4px 8px; border-radius:12px; font-size:0.8rem;">Đang ẩn</a>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="action-btns">
                                            <a href="edit.php?id=<?= $s['id'] ?>" class="btn-admin btn-edit"><i class="fas fa-edit"></i> Sửa</a>
                                            <a href="delete.php?id=<?= $s['id'] ?>" class="btn-admin btn-delete" onclick="return confirm('Bạn có chắc muốn xóa slide này?');"><i class="fas fa-trash"></i> Xóa</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($sliders)): ?>
                                <tr>
                                    <td colspan="5" style="text-align: center; padding: 20px;">Chưa có slide nào.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
</div>

<?php require_once '../includes/footer.php'; ?>

