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
    // Delete links from homestay_amenities first
    $pdo->prepare('DELETE FROM homestay_amenities WHERE amenity_id = ?')->execute([$id]);
    
    // Delete the amenity
    $stmt = $pdo->prepare('DELETE FROM amenities WHERE id = ?');
    $stmt->execute([$id]);
    
    setFlash('success', 'Đã xóa tiện nghi thành công.');
}

header('Location: index.php');
exit;

