<?php

require_once 'includes/config.php';
require_once __DIR__ . '/functions.php';

// Lấy ID địa điểm
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: ' . SITE_URL . '/places.php');
    exit;
}

// Lấy thông tin địa điểm
$stmt = $pdo->prepare("SELECT * FROM places WHERE id = ?");
$stmt->execute([$id]);
$place = $stmt->fetch();

if (!$place) {
    header('Location: ' . SITE_URL . '/404.php');
    exit;
}

// Tăng lượt xem
incrementViews($pdo, 'places', $id);

// Lấy ảnh gallery
$stmtImages = $pdo->prepare("SELECT * FROM place_images WHERE place_id = ? ORDER BY sort_order ASC");
$stmtImages->execute([$id]);
$galleryImages = $stmtImages->fetchAll();

$currentUser = getCurrentUser($pdo);
$reviewErrors = [];
$wishlisted = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_wishlist'])) {
    if (!isUserLoggedIn()) {
        header('Location: ' . SITE_URL . '/login.php?redirect=' . urlencode(SITE_URL . '/place-detail.php?id=' . $id));
        exit;
    }

    if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
        setFlash('error', 'Phiên làm việc không hợp lệ.');
    } else {
        $added = toggleWishlist($pdo, (int)$_SESSION['user_id'], $id);
        setFlash('success', $added ? 'Đã thêm địa điểm vào yêu thích.' : 'Đã xóa địa điểm khỏi yêu thích.');
    }
    header('Location: ' . SITE_URL . '/place-detail.php?id=' . $id);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    if (!isUserLoggedIn()) {
        header('Location: ' . SITE_URL . '/login.php?redirect=' . urlencode(SITE_URL . '/place-detail.php?id=' . $id));
        exit;
    }

    if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
        $reviewErrors[] = 'Phiên làm việc không hợp lệ.';
    } else {
        $rating = (int)($_POST['rating'] ?? 0);
        $content = trim($_POST['content'] ?? '');

        if ($rating < 1 || $rating > 5) {
            $reviewErrors[] = 'Điểm đánh giá phải từ 1 đến 5 sao.';
        }

        if (empty($reviewErrors)) {
            $stmtInsertReview = $pdo->prepare("INSERT INTO reviews (place_id, user_id, rating, content, is_approved) VALUES (?, ?, ?, ?, 1)");
            $stmtInsertReview->execute([$id, (int)$_SESSION['user_id'], $rating, $content]);
            setFlash('success', 'Cảm ơn bạn đã gửi đánh giá!');
            header('Location: ' . SITE_URL . '/place-detail.php?id=' . $id);
            exit;
        }
    }
}

$stmtReviewStats = $pdo->prepare("SELECT COUNT(*) as total, COALESCE(AVG(rating), 0) as avg_rating FROM reviews WHERE place_id = ? AND is_approved = 1");
$stmtReviewStats->execute([$id]);
$reviewStats = $stmtReviewStats->fetch();

$stmtReviews = $pdo->prepare("SELECT r.*, u.full_name FROM reviews r JOIN users u ON u.id = r.user_id WHERE r.place_id = ? AND r.is_approved = 1 ORDER BY r.created_at DESC LIMIT 20");
$stmtReviews->execute([$id]);
$reviews = $stmtReviews->fetchAll();

if ($currentUser) {
    $wishlisted = isPlaceWishlisted($pdo, (int)$currentUser['id'], $id);
}

// SEO
$pageTitle = $place['name'];
$pageDescription = excerpt(strip_tags($place['short_description'] ?? $place['description'] ?? ''), 160);
$ogImage = !empty($place['image']) ? getImageUrl($place['image'], 'places') : '';

require_once 'includes/header.php';
?>

<!-- Breadcrumb -->
<div class="breadcrumb">
    <div class="container">
        <ul class="breadcrumb-list">
            <li><a href="<?= SITE_URL ?>"><i class="fas fa-home"></i> Trang chủ</a></li>
            <li class="separator">/</li>
            <li><a href="<?= SITE_URL ?>/places.php">Địa điểm</a></li>
            <li class="separator">/</li>
            <li class="current"><?= sanitize($place['name']) ?></li>
        </ul>
    </div>
</div>

<!-- Chi tiết địa điểm -->
<section class="detail-section">
    <div class="container">
        <?= getFlash() ?>
        <!-- Header -->
        <div class="detail-header fade-in">
            <h1><?= sanitize($place['name']) ?></h1>
            <div class="detail-location">
                <i class="fas fa-map-marker-alt"></i>
                <span><?= sanitize($place['location'] ?? 'Vân Hồ, Sơn La') ?></span>
                <span style="margin-left:20px;color:var(--text-muted)">
                    <i class="far fa-eye"></i> <?= $place['views'] + 1 ?> lượt xem
                </span>
            </div>
            <div class="rating-summary">
                <strong><?= number_format((float)($reviewStats['avg_rating'] ?? 0), 1) ?>/5</strong>
                <span><?= (int)($reviewStats['total'] ?? 0) ?> đánh giá</span>
            </div>
            <div style="margin-top:12px">
                <form method="POST" action="">
                    <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                    <button type="submit" name="toggle_wishlist" class="btn btn-outline">
                        <i class="<?= $wishlisted ? 'fas' : 'far' ?> fa-heart" style="color:<?= $wishlisted ? '#ef4444' : '#64748b' ?>"></i>
                        <?= $wishlisted ? 'Bỏ yêu thích' : 'Thêm yêu thích' ?>
                    </button>
                </form>
            </div>
        </div>

        <!-- Ảnh chính -->
        <div class="detail-image fade-in">
            <?php if (!empty($place['image'])): ?>
                <img src="<?= getImageUrl($place['image'], 'places') ?>"
                    alt="<?= sanitize($place['name']) ?>" data-lightbox>
            <?php else: ?>
                <img src="https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=1200&h=500&fit=crop"
                    alt="<?= sanitize($place['name']) ?>">
            <?php endif; ?>
        </div>

        <!-- Nội dung mô tả -->
        <div class="detail-content fade-in">
            <?= $place['description'] ?? '' ?>
        </div>

        <!-- Bản đồ Google Maps -->
        <?php if (!empty($place['map_embed'])): ?>
            <div class="detail-map fade-in">
                <h2 style="margin-bottom:15px"><i class="fas fa-map" style="color:var(--primary)"></i> Bản đồ vị trí</h2>
                <?= $place['map_embed'] ?>
            </div>
        <?php endif; ?>

        <!-- Gallery ảnh phụ -->
        <?php if (count($galleryImages) > 0): ?>
            <div class="detail-gallery fade-in">
                <h2 style="margin-bottom:15px"><i class="fas fa-images" style="color:var(--primary)"></i> Hình ảnh</h2>
                <div class="swiper detail-gallery-swiper">
                    <div class="swiper-wrapper">
                        <?php foreach ($galleryImages as $img): ?>
                            <div class="swiper-slide">
                                <img src="<?= getImageUrl($img['image'], 'places') ?>"
                                    alt="<?= sanitize($place['name']) ?> - Ảnh <?= $img['sort_order'] ?>"
                                    data-lightbox="<?= getImageUrl($img['image'], 'places') ?>"
                                    loading="lazy">
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="swiper-button-next"></div>
                    <div class="swiper-button-prev"></div>
                    <div class="swiper-pagination"></div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Nút quay lại -->
        <div style="margin-top:40px">
            <a href="<?= SITE_URL ?>/places.php" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Quay lại danh sách
            </a>
        </div>

        <!-- Đánh giá -->
        <div class="review-block fade-in">
            <h2><i class="fas fa-star" style="color:var(--accent)"></i> Đánh giá từ du khách</h2>

            <?php if (!empty($reviewErrors)): ?>
                <div class="alert alert-error">
                    <?php foreach ($reviewErrors as $err): ?>
                        <div><?= sanitize($err) ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if ($currentUser): ?>
                <form method="POST" action="" class="review-form">
                    <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                    <div class="form-group">
                        <label for="rating">Chấm điểm</label>
                        <select name="rating" id="rating" class="form-control" required>
                            <option value="">Chọn số sao</option>
                            <?php for ($i = 5; $i >= 1; $i--): ?>
                                <option value="<?= $i ?>"><?= $i ?> sao</option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="content">Nội dung đánh giá</label>
                        <textarea name="content" id="content" class="form-control" rows="4" placeholder="Chia sẻ trải nghiệm của bạn..."></textarea>
                    </div>
                    <button type="submit" name="submit_review" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Gửi đánh giá
                    </button>
                </form>
            <?php else: ?>
                <div class="alert alert-success">
                    Đăng nhập để viết đánh giá. <a href="<?= SITE_URL ?>/login.php?redirect=<?= urlencode(SITE_URL . '/place-detail.php?id=' . $id) ?>">Đăng nhập ngay</a>
                </div>
            <?php endif; ?>

            <div class="review-list">
                <?php if (count($reviews) > 0): ?>
                    <?php foreach ($reviews as $review): ?>
                        <div class="review-item">
                            <div class="review-head">
                                <strong><?= sanitize($review['full_name']) ?></strong>
                                <span class="review-stars"><?= renderStars((int)$review['rating']) ?></span>
                            </div>
                            <div class="review-time"><?= formatDateTime($review['created_at']) ?></div>
                            <p><?= nl2br(sanitize($review['content'] ?? '')) ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="color:var(--text-light)">Chưa có đánh giá nào cho địa điểm này.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>

