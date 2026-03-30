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
    $stmt = $pdo->prepare('SELECT image FROM gallery WHERE id = ?');
    $stmt->execute([$id]);
    $img = $stmt->fetch();

    if ($img) {
        $imagePath = $img['image'];
        // Xóa slide trong DB
        $pdo->prepare('DELETE FROM gallery WHERE id = ?')->execute([$id]);
        
        // Xóa file vật lý
        if ($imagePath && strpos($imagePath, 'http') !== 0 && file_exists(dirname(dirname(__DIR__)) . $imagePath)) {
            @unlink(dirname(dirname(__DIR__)) . $imagePath);
        }
        
        setFlash('success', 'Đã xóa ảnh khỏi gallery.');
    } else {
        setFlash('error', 'Ảnh không tồn tại.');
    }
}

header('Location: index.php');
exit;

