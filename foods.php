<?php

$pageTitle = 'Ẩm thực đặc trưng';
$pageDescription = 'Khám phá các món ăn đặc sản hấp dẫn của xã Vân Hồ, Sơn La';

require_once 'includes/header.php';

$currentPageNum = getCurrentPage();
$perPage = ITEMS_PER_PAGE;
$keyword = trim($_GET['keyword'] ?? '');

$whereSql = '';
$params = [];
if ($keyword !== '') {
    $whereSql = ' WHERE name LIKE :keyword OR short_description LIKE :keyword OR description LIKE :keyword';
    $params[':keyword'] = '%' . $keyword . '%';
}

$stmtCount = $pdo->prepare('SELECT COUNT(*) FROM foods' . $whereSql);
foreach ($params as $key => $value) {
    $stmtCount->bindValue($key, $value);
}
$stmtCount->execute();
$totalFoods = (int)$stmtCount->fetchColumn();
$offset = ($currentPageNum - 1) * $perPage;

$stmt = $pdo->prepare('SELECT * FROM foods' . $whereSql . ' ORDER BY created_at DESC LIMIT :limit OFFSET :offset');
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$foods = $stmt->fetchAll();
?>

<div class="breadcrumb">
    <div class="container">
        <ul class="breadcrumb-list">
            <li><a href="<?= SITE_URL ?>"><i class="fas fa-home"></i> Trang chủ</a></li>
            <li class="separator">/</li>
            <li class="current">Ẩm thực</li>
        </ul>
    </div>
</div>

<div class="page-header">
    <div class="container">
        <h1><i class="fas fa-utensils" style="color:var(--accent)"></i> Ẩm thực đặc trưng Vân Hồ</h1>
        <p>Những món ăn mang đậm hương vị bản địa vùng cao Tây Bắc</p>
    </div>
</div>

<section class="section" style="padding-top:0;padding-bottom:25px">
    <div class="container">
        <form method="GET" action="" class="filter-form">
            <div class="filter-grid" style="grid-template-columns:2fr auto">
                <div class="form-group" style="margin-bottom:0">
                    <label for="keyword">Tìm món ăn</label>
                    <input type="text" id="keyword" name="keyword" class="form-control" value="<?= sanitize($keyword) ?>" placeholder="Ví dụ: thắng cố, cơm lam...">
                </div>
                <div style="display:flex;gap:10px;align-items:flex-end">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Tìm</button>
                    <a href="<?= SITE_URL ?>/foods.php" class="btn btn-outline">Đặt lại</a>
                </div>
            </div>
        </form>
    </div>
</section>

<section class="section" style="padding-top:20px">
    <div class="container">
        <?php if (count($foods) > 0): ?>
            <div class="cards-grid">
                <?php foreach ($foods as $food): ?>
                    <div class="card fade-in">
                        <div class="card-image">
                            <?php if (!empty($food['image'])): ?>
                                <img src="<?= getImageUrl($food['image'], 'foods') ?>" alt="<?= sanitize($food['name']) ?>" loading="lazy">
                            <?php else: ?>
                                <img src="https://images.unsplash.com/photo-1559847844-5315695dadae?w=400&h=250&fit=crop" alt="<?= sanitize($food['name']) ?>" loading="lazy">
                            <?php endif; ?>
                        </div>
                        <div class="card-body">
                            <h3><a href="<?= SITE_URL ?>/food-detail.php?id=<?= $food['id'] ?>"><?= sanitize($food['name']) ?></a></h3>
                            <p><?= excerpt($food['short_description'] ?? '', 120) ?></p>
                            <a href="<?= SITE_URL ?>/food-detail.php?id=<?= $food['id'] ?>" class="btn btn-outline">
                                Xem chi tiết <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <?= pagination($totalFoods, $perPage, $currentPageNum, SITE_URL . '/foods.php') ?>
        <?php else: ?>
            <div style="text-align:center;padding:50px 0">
                <i class="fas fa-utensils" style="font-size:4rem;color:var(--text-muted)"></i>
                <h3 style="margin-top:10px">Chưa có dữ liệu ẩm thực</h3>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>