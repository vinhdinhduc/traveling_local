<?php


$pageTitle = 'Địa điểm du lịch';
$pageDescription = 'Khám phá các địa điểm du lịch hấp dẫn tại xã Vân Hồ, huyện Vân Hồ, tỉnh Sơn La';

require_once 'includes/header.php';

// Phân trang
$currentPageNum = getCurrentPage();
$perPage = ITEMS_PER_PAGE;
$keyword = trim($_GET['keyword'] ?? '');
$locationFilter = trim($_GET['location'] ?? '');

$whereParts = [];
$params = [];
if ($keyword !== '') {
    $whereParts[] = '(name LIKE :keyword OR short_description LIKE :keyword OR description LIKE :keyword)';
    $params[':keyword'] = '%' . $keyword . '%';
}
if ($locationFilter !== '') {
    $whereParts[] = 'location LIKE :location';
    $params[':location'] = '%' . $locationFilter . '%';
}

$whereSql = count($whereParts) > 0 ? (' WHERE ' . implode(' AND ', $whereParts)) : '';

$stmtCount = $pdo->prepare("SELECT COUNT(*) FROM places" . $whereSql);
foreach ($params as $key => $value) {
    $stmtCount->bindValue($key, $value);
}
$stmtCount->execute();
$totalPlaces = (int)$stmtCount->fetchColumn();
$offset = ($currentPageNum - 1) * $perPage;

// Lấy danh sách địa điểm
$stmt = $pdo->prepare("SELECT * FROM places" . $whereSql . " ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$places = $stmt->fetchAll();
?>

<!-- Breadcrumb -->
<div class="breadcrumb">
    <div class="container">
        <ul class="breadcrumb-list">
            <li><a href="<?= SITE_URL ?>"><i class="fas fa-home"></i> Trang chủ</a></li>
            <li class="separator">/</li>
            <li class="current">Địa điểm du lịch</li>
        </ul>
    </div>
</div>

<!-- Page Header -->
<div class="page-header">
    <div class="container">
        <h1><i class="fas fa-map-marked-alt" style="color:var(--primary)"></i> Địa điểm du lịch Vân Hồ</h1>
        <p>Tổng cộng <?= $totalPlaces ?> địa điểm hấp dẫn đang chờ bạn khám phá</p>
    </div>
</div>

<!-- Bộ lọc -->
<section class="section" style="padding-top:0;padding-bottom:25px">
    <div class="container">
        <form method="GET" action="" class="filter-form">
            <div class="filter-grid">
                <div class="form-group" style="margin-bottom:0">
                    <label for="keyword">Từ khóa</label>
                    <input type="text" id="keyword" name="keyword" class="form-control"
                        placeholder="Tên địa điểm, mô tả..." value="<?= sanitize($keyword) ?>">
                </div>
                <div class="form-group" style="margin-bottom:0">
                    <label for="location">Khu vực</label>
                    <input type="text" id="location" name="location" class="form-control"
                        placeholder="Ví dụ: Bản Hua Tạt" value="<?= sanitize($locationFilter) ?>">
                </div>
                <div style="display:flex;gap:10px;align-items:flex-end">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Tìm kiếm</button>
                    <a href="<?= SITE_URL ?>/places.php" class="btn btn-outline">Đặt lại</a>
                </div>
            </div>
        </form>
    </div>
</section>

<!-- Danh sách địa điểm -->
<section class="section" style="padding-top:20px">
    <div class="container">
        <?php if (count($places) > 0): ?>
            <div class="cards-grid">
                <?php foreach ($places as $place): ?>
                    <div class="card fade-in">
                        <div class="card-image">
                            <?php if (!empty($place['image'])): ?>
                                <img src="<?= getImageUrl($place['image'], 'places') ?>"
                                    alt="<?= sanitize($place['name']) ?>" loading="lazy">
                            <?php else: ?>
                                <img src="https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=400&h=250&fit=crop"
                                    alt="<?= sanitize($place['name']) ?>" loading="lazy">
                            <?php endif; ?>
                        </div>
                        <div class="card-body">
                            <h3><a href="<?= SITE_URL ?>/place-detail.php?id=<?= $place['id'] ?>"><?= sanitize($place['name']) ?></a></h3>
                            <div class="card-meta">
                                <span><i class="fas fa-map-marker-alt"></i> <?= sanitize($place['location'] ?? 'Vân Hồ, Sơn La') ?></span>
                            </div>
                            <p><?= excerpt($place['short_description'] ?? '', 120) ?></p>
                            <a href="<?= SITE_URL ?>/place-detail.php?id=<?= $place['id'] ?>" class="btn btn-outline">
                                Xem chi tiết <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Phân trang -->
            <?= pagination($totalPlaces, $perPage, $currentPageNum, SITE_URL . '/places.php') ?>

        <?php else: ?>
            <div style="text-align:center;padding:60px 0">
                <i class="fas fa-map-marked-alt" style="font-size:4rem;color:var(--text-muted);margin-bottom:20px;display:block"></i>
                <h3>Chưa có địa điểm nào</h3>
                <p style="color:var(--text-light)">Các địa điểm du lịch sẽ được cập nhật sớm.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>