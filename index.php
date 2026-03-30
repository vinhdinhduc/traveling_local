<?php



$pageTitle = 'Trang chủ';
$pageDescription = 'Khám phá vẻ đẹp thiên nhiên hoang sơ và văn hóa đặc sắc tại xã Vân Hồ, huyện Vân Hồ, tỉnh Sơn La';

require_once 'includes/header.php';
require_once 'includes/settings.php';

// Lấy 6 địa điểm mới nhất
$stmtPlaces = $pdo->query("SELECT * FROM places ORDER BY created_at DESC LIMIT 6");
$places = $stmtPlaces->fetchAll();

// Lấy 3 tin tức mới nhất
$stmtNews = $pdo->query("SELECT * FROM news ORDER BY created_at DESC LIMIT 3");
$newsList = $stmtNews->fetchAll();

// Lấy ẩm thực nổi bật
$stmtFoods = $pdo->query("SELECT * FROM foods ORDER BY created_at DESC LIMIT 3");
$foods = $stmtFoods->fetchAll();
// Lấy homestay nổi bật
$stmtHomestays = $pdo->query("SELECT * FROM homestays ORDER BY created_at DESC LIMIT 3");
$homestays = $stmtHomestays->fetchAll();
?>

<!-- ===== HERO SLIDER (từ DB) ===== -->
<?php $sliders = getSliders($pdo); ?>
<section class="hero-section">
    <div class="swiper hero-slider">
        <div class="swiper-wrapper">
            <?php foreach ($sliders as $slide): ?>
                <div class="swiper-slide">
                    <img src="<?= './uploads/sliders/' . sanitize($slide['image']) ?>" alt="<?= sanitize($slide['title']) ?>" >
                    <div class="hero-overlay">
                        <div class="hero-content">
                            <h1><?= sanitize($slide['title']) ?></h1>
                            <?php if (!empty($slide['subtitle'])): ?>
                                <p><?= sanitize($slide['subtitle']) ?></p>
                            <?php endif; ?>
                            <?php if (!empty($slide['button_text']) && !empty($slide['button_url'])): ?>
                                <a href="<?= SITE_URL . sanitize($slide['button_url']) ?>" class="btn btn-accent"><?= sanitize($slide['button_text']) ?></a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="swiper-pagination"></div>
        <div class="swiper-button-next"></div>
        <div class="swiper-button-prev"></div>
    </div>
</section>

<!-- ===== ẨM THỰC ĐẶC TRƯNG ===== -->
<section class="section section-bg">
    <div class="container">
        <div class="section-title fade-in">
            <h2>Ẩm thực đặc trưng</h2>
            <p>Thưởng thức hương vị đặc sản vùng cao Vân Hồ</p>
        </div>

        <div class="cards-grid">
            <?php foreach ($foods as $food): ?>
                <div class="card fade-in">
                    <div class="card-image">
                        <?php if (!empty($food['image'])): ?>
                            <img src="<?= getImageUrl($food['image'], 'foods') ?>"
                                alt="<?= sanitize($food['name']) ?>" loading="lazy">
                        <?php else: ?>
                            <img src="./assets/images/img3.jpg"
                                alt="<?= sanitize($food['name']) ?>" loading="lazy">
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <h3><a href="<?= SITE_URL ?>/food-detail.php?id=<?= $food['id'] ?>"><?= sanitize($food['name']) ?></a></h3>
                        <p><?= excerpt($food['short_description'] ?? '', 120) ?></p>
                        <a href="<?= SITE_URL ?>/food-detail.php?id=<?= $food['id'] ?>" class="btn btn-outline btn-sm">
                            Xem món ăn <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="view-all fade-in">
            <a href="<?= SITE_URL ?>/foods.php" class="btn btn-primary">
                Xem tất cả món ăn <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</section>

<!-- ===== HOMESTAY NỔI BẬT ===== -->
<section class="section">
    <div class="container">
        <div class="section-title fade-in">
            <h2>Homestay nổi bật</h2>
            <p>Lưu trú gần thiên nhiên với không gian đậm chất bản địa</p>
        </div>

        <div class="cards-grid">
            <?php foreach ($homestays as $homestay): ?>
                <div class="card fade-in">
                    <div class="card-image">
                        <?php if (!empty($homestay['image'])): ?>
                            <img src="<?= getImageUrl($homestay['image'], 'homestays') ?>"
                                alt="<?= sanitize($homestay['name']) ?>" loading="lazy">
                        <?php else: ?>
                            <img src="https://images.unsplash.com/photo-1505693416388-ac5ce068fe85?w=400&h=250&fit=crop"
                                alt="<?= sanitize($homestay['name']) ?>" loading="lazy">
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <h3><a href="<?= SITE_URL ?>/homestay-detail.php?id=<?= $homestay['id'] ?>"><?= sanitize($homestay['name']) ?></a></h3>
                        <div class="card-meta">
                            <span><i class="fas fa-money-bill-wave"></i> Từ <?= formatPrice((float)($homestay['price_per_night'] ?? 0)) ?>/đêm</span>
                        </div>
                        <p><?= excerpt($homestay['short_description'] ?? '', 100) ?></p>
                        <a href="<?= SITE_URL ?>/homestay-detail.php?id=<?= $homestay['id'] ?>" class="btn btn-outline btn-sm">
                            Đặt homestay <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="view-all fade-in">
            <a href="<?= SITE_URL ?>/homestays.php" class="btn btn-secondary">
                Xem tất cả homestay <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</section>

<!-- ===== GIỚI THIỆU (từ DB) ===== -->
<section class="section section-bg">
    <div class="container">
        <div class="intro-section fade-in">
            <div class="intro-text">
                <h2><?= sanitize(getSetting($pdo, 'about_title', 'Khám phá Vân Hồ')) ?></h2>
                <?php
                $aboutContent = getSetting($pdo, 'about_content', '');
                $aboutParagraphs = array_filter(explode("\n", $aboutContent));
                foreach ($aboutParagraphs as $paragraph):
                    $paragraph = trim($paragraph);
                    if ($paragraph !== ''):
                ?>
                    <p><?= sanitize($paragraph) ?></p>
                <?php endif; endforeach; ?>
                <a href="<?= SITE_URL ?>/places.php" class="btn btn-primary" style="margin-top:10px">
                    <i class="fas fa-compass"></i> Khám phá địa điểm
                </a>
            </div>
            <div class="intro-image">
                <img src="<?= sanitize(getSetting($pdo, 'about_image', './assets/images/img3.jpg')) ?>"
                    alt="Cảnh đẹp Vân Hồ - Sơn La" loading="lazy">
            </div>
        </div>
    </div>
</section>

<!-- ===== ĐỊA ĐIỂM NỔI BẬT ===== -->
<section class="section">
    <div class="container">
        <div class="section-title fade-in">
            <h2>Địa điểm nổi bật</h2>
            <p>Những điểm đến hấp dẫn không thể bỏ qua khi tới Vân Hồ</p>
        </div>

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
                        <p><?= excerpt($place['short_description'] ?? '', 120) ?></p>
                        <a href="<?= SITE_URL ?>/place-detail.php?id=<?= $place['id'] ?>" class="btn btn-outline btn-sm">
                            Xem chi tiết <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="view-all fade-in">
            <a href="<?= SITE_URL ?>/places.php" class="btn btn-primary">
                Xem tất cả địa điểm <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</section>

<!-- ===== GALLERY SWIPER (từ DB) ===== -->
<?php $galleryImages = getGalleryImages($pdo); ?>
<section class="section section-bg gallery-section">
    <div class="container">
        <div class="section-title fade-in">
            <h2>Hình ảnh Vân Hồ</h2>
            <p>Những khoảnh khắc đẹp nhất được ghi lại từ Vân Hồ</p>
        </div>

        <div class="swiper gallery-swiper fade-in">
            <div class="swiper-wrapper">
                <?php foreach ($galleryImages as $gImg): ?>
                    <div class="swiper-slide">
                         <img src="<?= './uploads/gallery/' . sanitize($gImg['image']) ?>" alt="<?= sanitize($gImg['alt_text'] ?? 'Ảnh Vân Hồ') ?>" >
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="swiper-button-next"></div>
            <div class="swiper-button-prev"></div>
        </div>
    </div>
</section>

<!-- ===== TIN TỨC MỚI NHẤT ===== -->
<section class="section">
    <div class="container">
        <div class="section-title fade-in">
            <h2>Tin tức mới nhất</h2>
            <p>Cập nhật thông tin du lịch và sự kiện tại Vân Hồ</p>
        </div>

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
                        <p><?= excerpt($news['excerpt'] ?? strip_tags($news['content'] ?? ''), 100) ?></p>
                        <a href="<?= SITE_URL ?>/news-detail.php?id=<?= $news['id'] ?>" class="btn btn-outline btn-sm">
                            Đọc thêm <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="view-all fade-in">
            <a href="<?= SITE_URL ?>/news.php" class="btn btn-secondary">
                Xem tất cả tin tức <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>