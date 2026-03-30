<?php

$pageTitle = 'Homestay Vân Hồ';
$pageDescription = 'Danh sách homestay nổi bật tại xã Vân Hồ, Sơn La';

require_once 'includes/header.php';

$currentPageNum = getCurrentPage();
$perPage = ITEMS_PER_PAGE;
$keyword = trim($_GET['keyword'] ?? '');
$maxPrice = (int)($_GET['max_price'] ?? 0);
$sort = $_GET['sort'] ?? 'newest';

$allowedSort = [
    'newest' => 'created_at DESC',
    'price_asc' => 'price_per_night ASC',
    'price_desc' => 'price_per_night DESC',
    'popular' => 'views DESC, created_at DESC'
];
if (!isset($allowedSort[$sort])) {
    $sort = 'newest';
}

$whereParts = [];
$params = [];
if ($keyword !== '') {
    $whereParts[] = '(name LIKE :keyword OR short_description LIKE :keyword OR description LIKE :keyword OR address LIKE :keyword)';
    $params[':keyword'] = '%' . $keyword . '%';
}
if ($maxPrice > 0) {
    $whereParts[] = 'price_per_night <= :max_price';
    $params[':max_price'] = $maxPrice;
}
$whereSql = count($whereParts) > 0 ? (' WHERE ' . implode(' AND ', $whereParts)) : '';

$stmtCount = $pdo->prepare('SELECT COUNT(*) FROM homestays' . $whereSql);
foreach ($params as $key => $value) {
    $stmtCount->bindValue($key, $value);
}
$stmtCount->execute();
$totalHomestays = (int)$stmtCount->fetchColumn();
$offset = ($currentPageNum - 1) * $perPage;

$stmt = $pdo->prepare('SELECT * FROM homestays' . $whereSql . ' ORDER BY ' . $allowedSort[$sort] . ' LIMIT :limit OFFSET :offset');
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$homestays = $stmt->fetchAll();
?>

<div class="breadcrumb">
    <div class="container">
        <ul class="breadcrumb-list">
            <li><a href="<?= SITE_URL ?>"><i class="fas fa-home"></i> Trang chủ</a></li>
            <li class="separator">/</li>
            <li class="current">Homestay</li>
        </ul>
    </div>
</div>

<div class="page-header">
    <div class="container">
        <h1><i class="fas fa-house" style="color:var(--secondary)"></i> Homestay Vân Hồ</h1>
        <p>Lựa chọn chỗ nghỉ phù hợp cho hành trình khám phá của bạn</p>
    </div>
</div>

<section class="section" style="padding-top:0;padding-bottom:25px">
    <div class="container">
        <form method="GET" action="" class="filter-form">
            <div class="filter-grid homestay-filter-grid">
                <div class="form-group" style="margin-bottom:0">
                    <label for="keyword">Từ khóa</label>
                    <input type="text" id="keyword" name="keyword" class="form-control" value="<?= sanitize($keyword) ?>" placeholder="Tên, địa chỉ homestay...">
                </div>
                <div class="form-group" style="margin-bottom:0">
                    <label for="max_price">Giá tối đa / đêm</label>
                    <select name="max_price" id="max_price" class="form-control">
                        <option value="0">Không giới hạn</option>
                        <option value="500000" <?= $maxPrice === 500000 ? 'selected' : '' ?>>Dưới 500.000đ</option>
                        <option value="800000" <?= $maxPrice === 800000 ? 'selected' : '' ?>>Dưới 800.000đ</option>
                        <option value="1200000" <?= $maxPrice === 1200000 ? 'selected' : '' ?>>Dưới 1.200.000đ</option>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom:0">
                    <label for="sort">Sắp xếp</label>
                    <select name="sort" id="sort" class="form-control">
                        <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Mới nhất</option>
                        <option value="popular" <?= $sort === 'popular' ? 'selected' : '' ?>>Xem nhiều</option>
                        <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>Giá tăng dần</option>
                        <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Giá giảm dần</option>
                    </select>
                </div>
                <div style="display:flex;gap:10px;align-items:flex-end">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Lọc</button>
                    <a href="<?= SITE_URL ?>/homestays.php" class="btn btn-outline">Đặt lại</a>
                </div>
            </div>
        </form>
    </div>
</section>

<section class="section" style="padding-top:20px">
    <div class="container">
        <?php if (count($homestays) > 0): ?>
            <div class="cards-grid">
                <?php foreach ($homestays as $homestay): ?>
                    <div class="card fade-in">
                        <div class="card-image">
                            <?php if (!empty($homestay['image'])): ?>
                                <img src="<?= getImageUrl($homestay['image'], 'homestays') ?>" alt="<?= sanitize($homestay['name']) ?>" loading="lazy">
                            <?php else: ?>
                                <img src="https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?w=400&h=250&fit=crop" alt="<?= sanitize($homestay['name']) ?>" loading="lazy">
                            <?php endif; ?>
                        </div>
                        <div class="card-body">
                            <h3><a href="<?= SITE_URL ?>/homestay-detail.php?id=<?= $homestay['id'] ?>"><?= sanitize($homestay['name']) ?></a></h3>
                            <div class="card-meta">
                                <span><i class="fas fa-money-bill-wave"></i> <?= formatPrice((float)($homestay['price_per_night'] ?? 0)) ?>/đêm</span>
                            </div>
                            <p><?= excerpt($homestay['short_description'] ?? '', 100) ?></p>
                            <a href="<?= SITE_URL ?>/homestay-detail.php?id=<?= $homestay['id'] ?>" class="btn btn-outline">
                                Xem và đặt phòng <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php $query = buildQueryString(['page' => null]); ?>
            <?= pagination($totalHomestays, $perPage, $currentPageNum, SITE_URL . '/homestays.php' . ($query !== '' ? ('?' . $query) : '')) ?>
        <?php else: ?>
            <div style="text-align:center;padding:50px 0">
                <i class="fas fa-house" style="font-size:4rem;color:var(--text-muted)"></i>
                <h3 style="margin-top:10px">Chưa có homestay phù hợp</h3>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>