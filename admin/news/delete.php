<?php



require_once dirname(__DIR__, 2) . '/includes/config.php';
require_once dirname(__DIR__, 2) . '/functions.php';
requireLogin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: ' . ADMIN_URL . '/news/');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM news WHERE id = ?");
$stmt->execute([$id]);
$news = $stmt->fetch();

if (!$news) {
    setFlash('error', 'Không tìm thấy bài viết.');
    header('Location: ' . ADMIN_URL . '/news/');
    exit;
}

// Xóa ảnh
if (!empty($news['image'])) {
    deleteImage($news['image'], 'news');
}

// Xóa record
$stmtDel = $pdo->prepare("DELETE FROM news WHERE id = ?");
$stmtDel->execute([$id]);

setFlash('success', 'Đã xóa bài viết "' . $news['title'] . '" thành công.');
header('Location: ' . ADMIN_URL . '/news/');
exit;

