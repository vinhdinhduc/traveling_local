<?php


$pageTitle = 'Tin tức du lịch';
$pageDescription = 'Cập nhật tin tức, sự kiện và cẩm nang du lịch tại Vân Hồ, Sơn La';

require_once 'includes/header.php';

// Phân trang
$currentPageNum = getCurrentPage();
$perPage = NEWS_PER_PAGE;
$totalNews = countRecords($pdo, 'news');
$offset = ($currentPageNum - 1) * $perPage;

// Lấy danh sách tin tức
$stmt = $pdo->prepare("SELECT * FROM news ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$newsList = $stmt->fetchAll();
?>

<!-- Breadcrumb -->
<div class="breadcrumb">
    <div class="container">
        <ul class="breadcrumb-list">
            <li><a href="<?= SITE_URL ?>"><i class="fas fa-home"></i> Trang chủ</a></li>
            <li class="separator">/</li>
            <li class="current">Tin tức</li>
        </ul>
    </div>
</div>

<!-- Page Header -->
<div class="page-header">
    <div class="container">
        <h1><i class="fas fa-newspaper" style="color:var(--primary)"></i> Tin tức du lịch Vân Hồ</h1>
        <p>Cập nhật thông tin mới nhất về du lịch và sự kiện</p>
    </div>
</div>

<!-- Danh sách tin tức -->
<section class="section" style="padding-top:20px">
    <div class="container">
        <?php if (count($newsList) > 0): ?>
            <div class="cards-grid">
                <?php foreach ($newsList as $news): ?>
                    <div class="card fade-in">
                        <div class="card-image">
                            <?php if (!empty($news['image'])): ?>
                                <img src="<?= getImageUrl($news['image'], 'news') ?>"
                                    alt="<?= sanitize($news['title']) ?>" loading="lazy">
                            <?php else: ?>
                                <img src="https://images.unsplash.com/photo-1504150558240-0b4fd8946624?w=400&h=250&fit=crop"
                                    alt="<?= sanitize($news['title']) ?>" loading="lazy">
                            <?php endif; ?>
                        </div>
                        <div class="card-body">
                            <div class="card-meta">
                                <span><i class="far fa-calendar-alt"></i> <?= formatDate($news['created_at']) ?></span>
                                <span><i class="far fa-eye"></i> <?= $news['views'] ?> lượt xem</span>
                            </div>
                            <h3><a href="<?= SITE_URL ?>/news-detail.php?id=<?= $news['id'] ?>"><?= sanitize($news['title']) ?></a></h3>
                            <p><?= excerpt($news['excerpt'] ?? strip_tags($news['content'] ?? ''), 120) ?></p>
                            <a href="<?= SITE_URL ?>/news-detail.php?id=<?= $news['id'] ?>" class="btn btn-outline">
                                Đọc thêm <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Phân trang -->
            <?= pagination($totalNews, $perPage, $currentPageNum, SITE_URL . '/news.php') ?>

        <?php else: ?>
            <div style="text-align:center;padding:60px 0">
                <i class="fas fa-newspaper" style="font-size:4rem;color:var(--text-muted);margin-bottom:20px;display:block"></i>
                <h3>Chưa có tin tức nào</h3>
                <p style="color:var(--text-light)">Tin tức sẽ được cập nhật sớm.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>