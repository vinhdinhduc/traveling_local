<?php


$pageTitle = 'Địa điểm du lịch';
$pageDescription = 'Khám phá các địa điểm du lịch hấp dẫn tại xã Vân Hồ, huyện Vân Hồ, tỉnh Sơn La';

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_wishlist'])) {
    if (!isUserLoggedIn()) {
        header('Location: ' . SITE_URL . '/login.php?redirect=' . urlencode(SITE_URL . '/places.php?' . buildQueryString()));
        exit;
    }

    if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
        setFlash('error', 'Phiên làm việc không hợp lệ.');
    } else {
        $wishPlaceId = (int)($_POST['place_id'] ?? 0);
        if ($wishPlaceId > 0) {
            $added = toggleWishlist($pdo, (int)$_SESSION['user_id'], $wishPlaceId);
            setFlash('success', $added ? 'Đã thêm vào danh sách yêu thích.' : 'Đã xóa khỏi danh sách yêu thích.');
        }
    }
    header('Location: ' . SITE_URL . '/places.php?' . buildQueryString());
    exit;
}

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

$wishlistMap = [];
if (isUserLoggedIn() && count($places) > 0) {
    $placeIds = array_map(static fn(array $p): int => (int)$p['id'], $places);
    $placeIds = array_values(array_unique($placeIds));
    if (count($placeIds) > 0) {
        $placeIdsSql = implode(',', array_fill(0, count($placeIds), '?'));
        $stmtWishlist = $pdo->prepare('SELECT place_id FROM wishlists WHERE user_id = ? AND place_id IN (' . $placeIdsSql . ')');
        $stmtWishlist->execute(array_merge([(int)$_SESSION['user_id']], $placeIds));
        foreach ($stmtWishlist->fetchAll() as $row) {
            $wishlistMap[(int)$row['place_id']] = true;
        }
    }
}

require_once 'includes/header.php';
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
        <?= getFlash() ?>
        <?php if (count($places) > 0): ?>
            <div class="cards-grid">
                <?php foreach ($places as $place): ?>
                    <div class="card fade-in">
                        <div class="card-image">
                            <form method="POST" action="" style="position:absolute;top:10px;right:10px;z-index:3">
                                <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                                <input type="hidden" name="place_id" value="<?= (int)$place['id'] ?>">
                                <button type="submit" name="toggle_wishlist" class="btn" style="padding:7px 10px;background:#fff;border:1px solid #e2e8f0">
                                    <?php $liked = !empty($wishlistMap[(int)$place['id']]); ?>
                                    <i class="<?= $liked ? 'fas' : 'far' ?> fa-heart" style="color:<?= $liked ? '#ef4444' : '#64748b' ?>"></i>
                                </button>
                            </form>
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