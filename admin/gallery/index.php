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
    $stmt = $pdo->prepare('UPDATE gallery SET is_active = NOT is_active WHERE id = ?');
    $stmt->execute([$id]);
    setFlash('success', 'Đã thay đổi trạng thái ảnh gallery.');
    header('Location: index.php');
    exit;
}

$stmt = $pdo->query('SELECT * FROM gallery ORDER BY sort_order ASC, id DESC');
$images = $stmt->fetchAll();

$pageTitle = 'Quản lý Gallery';
require_once '../includes/header.php';
?>

<div class="content-header">
    <h1><i class="far fa-images" style="color:var(--admin-primary)"></i> Quản lý Gallery Ảnh</h1>
    <a href="add.php" class="btn-admin btn-add"><i class="fas fa-plus"></i> Thêm ảnh mới</a>
</div>

<div class="fade-in">

                <div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 20px;">
                    <?php foreach ($images as $img): ?>
                        <div style="border:1px solid #ddd; border-radius:8px; overflow:hidden; position:relative; background:#fff;">
                            <img src="<?= getImageUrl($img['image'], 'gallery') ?>" alt="<?= sanitize($img['alt_text']) ?>" style="width:100%; height:140px; object-fit:cover; display:block;">
                            
                            <div style="padding: 10px; font-size: 0.85rem;">
                                <div style="margin-bottom: 5px; color:#555; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                    <strong>Alt:</strong> <?= sanitize($img['alt_text']) ?>
                                </div>
                                <div style="margin-bottom: 10px; color:#777;">
                                    <strong>Thứ tự:</strong> <?= (int)$img['sort_order'] ?>
                                </div>
                                
                                <div style="display:flex; justify-content:space-between; align-items:center;">
                                    <?php if ($img['is_active']): ?>
                                        <a href="?toggle=1&id=<?= $img['id'] ?>" class="badge" style="background:#cdf5d7; color:#155724; text-decoration:none; padding:4px 8px; border-radius:12px;">Đang hiện</a>
                                    <?php else: ?>
                                        <a href="?toggle=1&id=<?= $img['id'] ?>" class="badge" style="background:#fde8e8; color:#721c24; text-decoration:none; padding:4px 8px; border-radius:12px;">Đang ẩn</a>
                                    <?php endif; ?>
                                    
                                    <a href="delete.php?id=<?= $img['id'] ?>" style="color:#c62828; text-decoration:none;" onclick="return confirm('Xóa ảnh này ra khỏi Gallery?');"><i class="fas fa-trash"></i> Xóa</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php if (empty($images)): ?>
                    <p style="text-align: center; padding: 20px; color: #666;">Chưa có ảnh nào trong gallery.</p>
                <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>

