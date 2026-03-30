<?php

require_once dirname(__DIR__, 2) . '/includes/config.php';
require_once dirname(__DIR__, 2) . '/functions.php';
requireLogin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: ' . ADMIN_URL . '/homestays/');
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM homestays WHERE id = ?');
$stmt->execute([$id]);
$homestay = $stmt->fetch();
if (!$homestay) {
    setFlash('error', 'Không tìm thấy homestay.');
    header('Location: ' . ADMIN_URL . '/homestays/');
    exit;
}

if (!empty($homestay['image'])) {
    deleteImage($homestay['image'], 'homestays');
}

$pdo->prepare('DELETE FROM homestays WHERE id = ?')->execute([$id]);
setFlash('success', 'Đã xóa homestay thành công.');
header('Location: ' . ADMIN_URL . '/homestays/');
exit;

