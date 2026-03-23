<?php

require_once 'includes/config.php';
require_once 'includes/functions.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: ' . SITE_URL . '/foods.php');
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM foods WHERE id = ?');
$stmt->execute([$id]);
$food = $stmt->fetch();

if (!$food) {
    header('Location: ' . SITE_URL . '/404.php');
    exit;
}

incrementViews($pdo, 'foods', $id);

$pageTitle = $food['name'];
$pageDescription = excerpt(strip_tags($food['short_description'] ?? $food['description'] ?? ''), 160);

require_once 'includes/header.php';
?>

<div class="breadcrumb">
    <div class="container">
        <ul class="breadcrumb-list">
            <li><a href="<?= SITE_URL ?>"><i class="fas fa-home"></i> Trang chủ</a></li>
            <li class="separator">/</li>
            <li><a href="<?= SITE_URL ?>/foods.php">Ẩm thực</a></li>
            <li class="separator">/</li>
            <li class="current"><?= sanitize($food['name']) ?></li>
        </ul>
    </div>
</div>

<section class="detail-section">
    <div class="container">
        <div class="detail-header fade-in">
            <h1><?= sanitize($food['name']) ?></h1>
            <div class="detail-location">
                <i class="fas fa-eye"></i>
                <span><?= $food['views'] + 1 ?> lượt xem</span>
            </div>
        </div>

        <div class="detail-image fade-in">
            <?php if (!empty($food['image'])): ?>
                <img src="<?= getImageUrl($food['image'], 'foods') ?>" alt="<?= sanitize($food['name']) ?>" data-lightbox>
            <?php else: ?>
                <img src="https://images.unsplash.com/photo-1526318896980-cf78c088247c?w=1200&h=500&fit=crop" alt="<?= sanitize($food['name']) ?>">
            <?php endif; ?>
        </div>

        <div class="detail-content fade-in">
            <?= $food['description'] ?: '<p>' . sanitize($food['short_description'] ?? '') . '</p>' ?>
        </div>

        <div style="margin-top:35px">
            <a href="<?= SITE_URL ?>/foods.php" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Quay lại danh sách món ăn
            </a>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>