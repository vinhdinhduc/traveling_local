<?php

require_once dirname(__DIR__, 2) . '/includes/config.php';
require_once dirname(__DIR__, 2) . '/includes/functions.php';
requireLogin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: ' . ADMIN_URL . '/foods/');
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM foods WHERE id = ?');
$stmt->execute([$id]);
$food = $stmt->fetch();
if (!$food) {
    setFlash('error', 'Không tìm thấy món ăn.');
    header('Location: ' . ADMIN_URL . '/foods/');
    exit;
}

if (!empty($food['image'])) {
    deleteImage($food['image'], 'foods');
}

$pdo->prepare('DELETE FROM foods WHERE id = ?')->execute([$id]);
setFlash('success', 'Đã xóa món ăn thành công.');
header('Location: ' . ADMIN_URL . '/foods/');
exit;
