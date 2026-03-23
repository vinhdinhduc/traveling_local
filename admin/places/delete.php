<?php

/**
 * ADMIN - XÓA ĐỊA ĐIỂM
 * Xóa ảnh + record trong database
 */

require_once dirname(__DIR__, 2) . '/includes/config.php';
require_once dirname(__DIR__, 2) . '/includes/functions.php';
requireLogin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: ' . ADMIN_URL . '/places/');
    exit;
}

// Lấy thông tin địa điểm
$stmt = $pdo->prepare("SELECT * FROM places WHERE id = ?");
$stmt->execute([$id]);
$place = $stmt->fetch();

if (!$place) {
    setFlash('error', 'Không tìm thấy địa điểm.');
    header('Location: ' . ADMIN_URL . '/places/');
    exit;
}

// Xóa ảnh chính
if (!empty($place['image'])) {
    deleteImage($place['image'], 'places');
}

// Xóa tất cả ảnh gallery
$stmtImages = $pdo->prepare("SELECT image FROM place_images WHERE place_id = ?");
$stmtImages->execute([$id]);
$images = $stmtImages->fetchAll();
foreach ($images as $img) {
    deleteImage($img['image'], 'places');
}

// Xóa record (place_images sẽ tự xóa nhờ ON DELETE CASCADE)
$stmtDel = $pdo->prepare("DELETE FROM places WHERE id = ?");
$stmtDel->execute([$id]);

setFlash('success', 'Đã xóa địa điểm "' . $place['name'] . '" thành công.');
header('Location: ' . ADMIN_URL . '/places/');
exit;
