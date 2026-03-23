<?php



require_once 'includes/config.php';
require_once 'includes/functions.php';

// Lấy ID bài viết
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: ' . SITE_URL . '/news.php');
    exit;
}

// Lấy thông tin bài viết
$stmt = $pdo->prepare("SELECT * FROM news WHERE id = ?");
$stmt->execute([$id]);
$news = $stmt->fetch();

if (!$news) {
    header('Location: ' . SITE_URL . '/404.php');
    exit;
}

// Tăng lượt xem
incrementViews($pdo, 'news', $id);

// Lấy 3 bài viết liên quan (gần nhất, trừ bài hiện tại)
$stmtRelated = $pdo->prepare("SELECT * FROM news WHERE id != ? ORDER BY created_at DESC LIMIT 3");
$stmtRelated->execute([$id]);
$relatedNews = $stmtRelated->fetchAll();

// SEO
$pageTitle = $news['title'];
$pageDescription = excerpt(strip_tags($news['excerpt'] ?? $news['content'] ?? ''), 160);
$ogImage = !empty($news['image']) ? getImageUrl($news['image'], 'news') : '';

require_once 'includes/header.php';
?>

<!-- Breadcrumb -->
<div class="breadcrumb">
    <div class="container">
        <ul class="breadcrumb-list">
            <li><a href="<?= SITE_URL ?>"><i class="fas fa-home"></i> Trang chủ</a></li>
            <li class="separator">/</li>
            <li><a href="<?= SITE_URL ?>/news.php">Tin tức</a></li>
            <li class="separator">/</li>
            <li class="current"><?= sanitize(excerpt($news['title'], 50)) ?></li>
        </ul>
    </div>
</div>

<!-- Chi tiết tin tức -->
<section class="detail-section news-detail">
    <div class="container">
        <!-- Header -->
        <div class="detail-header fade-in">
            <h1><?= sanitize($news['title']) ?></h1>
            <div class="detail-meta">
                <span><i class="far fa-calendar-alt"></i> <?= formatDate($news['created_at']) ?></span>
                <span><i class="far fa-eye"></i> <?= $news['views'] + 1 ?> lượt xem</span>
            </div>
        </div>

        <!-- Ảnh đại diện -->
        <div class="detail-image fade-in">
            <?php if (!empty($news['image'])): ?>
                <img src="<?= getImageUrl($news['image'], 'news') ?>"
                    alt="<?= sanitize($news['title']) ?>">
            <?php else: ?>
                <img src="https://images.unsplash.com/photo-1504150558240-0b4fd8946624?w=1200&h=500&fit=crop"
                    alt="<?= sanitize($news['title']) ?>">
            <?php endif; ?>
        </div>

        <!-- Nội dung bài viết -->
        <article class="detail-content fade-in">
            <?= $news['content'] ?? '' ?>
        </article>

        <!-- Nút quay lại -->
        <div style="margin-top:40px">
            <a href="<?= SITE_URL ?>/news.php" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Quay lại danh sách
            </a>
        </div>

        <!-- Tin tức liên quan -->
        <?php if (count($relatedNews) > 0): ?>
            <div class="related-news fade-in">
                <h2><i class="fas fa-newspaper" style="color:var(--primary)"></i> Tin tức liên quan</h2>
                <div class="cards-grid">
                    <?php foreach ($relatedNews as $related): ?>
                        <div class="card">
                            <div class="card-image">
                                <?php if (!empty($related['image'])): ?>
                                    <img src="<?= getImageUrl($related['image'], 'news') ?>"
                                        alt="<?= sanitize($related['title']) ?>" loading="lazy">
                                <?php else: ?>
                                    <img src="https://images.unsplash.com/photo-1504150558240-0b4fd8946624?w=400&h=250&fit=crop"
                                        alt="<?= sanitize($related['title']) ?>" loading="lazy">
                                <?php endif; ?>
                            </div>
                            <div class="card-body">
                                <div class="card-meta">
                                    <span><i class="far fa-calendar-alt"></i> <?= formatDate($related['created_at']) ?></span>
                                </div>
                                <h3><a href="<?= SITE_URL ?>/news-detail.php?id=<?= $related['id'] ?>"><?= sanitize($related['title']) ?></a></h3>
                                <p><?= excerpt($related['excerpt'] ?? strip_tags($related['content'] ?? ''), 80) ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>