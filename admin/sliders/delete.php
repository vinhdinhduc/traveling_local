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

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id > 0) {
    $stmt = $pdo->prepare('SELECT image FROM sliders WHERE id = ?');
    $stmt->execute([$id]);
    $slider = $stmt->fetch();

    if ($slider) {
        $imagePath = $slider['image'];
        // Xóa slide trong DB
        $pdo->prepare('DELETE FROM sliders WHERE id = ?')->execute([$id]);
        
        // Xóa file vật lý
        if ($imagePath && strpos($imagePath, 'http') !== 0 && file_exists(dirname(dirname(__DIR__)) . $imagePath)) {
            @unlink(dirname(dirname(__DIR__)) . $imagePath);
        }
        
        setFlash('success', 'Đã xóa slider thành công.');
    } else {
        setFlash('error', 'Slider không tồn tại.');
    }
}

header('Location: index.php');
exit;

