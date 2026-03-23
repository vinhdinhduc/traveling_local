<?php



$pageTitle = 'Trang chủ';
$pageDescription = 'Khám phá vẻ đẹp thiên nhiên hoang sơ và văn hóa đặc sắc tại xã Vân Hồ, huyện Vân Hồ, tỉnh Sơn La';

require_once 'includes/header.php';

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

<!-- ===== HERO SLIDER ===== -->
<section class="hero-section">
    <div class="swiper hero-slider">
        <div class="swiper-wrapper">
            <!-- Slide 1 -->
            <div class="swiper-slide">
                <img src="https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=1400&h=600&fit=crop"
                    alt="Cảnh đẹp Vân Hồ - Núi non hùng vĩ" loading="lazy">
                <div class="hero-overlay">
                    <div class="hero-content">
                        <h1>Khám phá Vân Hồ</h1>
                        <p>Thiên đường du lịch giữa đại ngàn Tây Bắc</p>
                        <a href="<?= SITE_URL ?>/places.php" class="btn btn-accent">Khám phá ngay</a>
                    </div>
                </div>
            </div>
            <!-- Slide 2 -->
            <div class="swiper-slide">
                <img src="./assets/images/img2.jpg"
                    alt="Bản làng Vân Hồ" loading="lazy">
                <div class="hero-overlay">
                    <div class="hero-content">
                        <h1>Bản làng yên bình</h1>
                        <p>Trải nghiệm cuộc sống bình dị nơi bản làng người Mông</p>
                        <a href="<?= SITE_URL ?>/places.php" class="btn btn-accent">Xem địa điểm</a>
                    </div>
                </div>
            </div>
            <!-- Slide 3 -->
            <div class="swiper-slide">
                <img src="./assets/images/img1.jpg"
                    alt="Thiên nhiên Vân Hồ" loading="lazy">
                <div class="hero-overlay">
                    <div class="hero-content">
                        <h1>Thiên nhiên tươi đẹp</h1>
                        <p>Hòa mình vào thiên nhiên hoang sơ, hùng vĩ</p>
                        <a href="<?= SITE_URL ?>/contact.php" class="btn btn-accent">Liên hệ tư vấn</a>
                    </div>
                </div>
            </div>
            <!-- Slide 4 -->
            <div class="swiper-slide">
                <img src="https://images.unsplash.com/photo-1470071459604-3b5ec3a7fe05?w=1400&h=600&fit=crop"
                    alt="Rừng núi Vân Hồ" loading="lazy">
                <div class="hero-overlay">
                    <div class="hero-content">
                        <h1>Văn hóa đặc sắc</h1>
                        <p>Khám phá nét văn hóa truyền thống độc đáo của đồng bào dân tộc</p>
                        <a href="<?= SITE_URL ?>/news.php" class="btn btn-accent">Đọc thêm</a>
                    </div>
                </div>
            </div>
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

<!-- ===== GIỚI THIỆU ===== -->
<section class="section section-bg">
    <div class="container">
        <div class="intro-section fade-in">
            <div class="intro-text">
                <h2>Khám phá Vân Hồ</h2>
                <p>Vân Hồ là một xã thuộc huyện Vân Hồ, tỉnh Sơn La, nằm ở độ cao trung bình trên 1.000m so với mực nước biển. Nơi đây được thiên nhiên ưu đãi với khí hậu mát mẻ quanh năm, cảnh quan hùng vĩ và hệ sinh thái phong phú.</p>
                <p>Đến với Vân Hồ, du khách sẽ được trải nghiệm cuộc sống bình dị tại các bản làng của đồng bào Mông, Dao, Thái - nơi lưu giữ những giá trị văn hóa truyền thống đặc sắc qua bao thế hệ.</p>
                <p>Từ những cánh rừng thông xanh mướt, thác nước hùng vĩ đến những đồi chè bát ngát, mỗi góc nhìn tại Vân Hồ đều mang lại cảm xúc khó quên cho du khách.</p>
                <a href="<?= SITE_URL ?>/places.php" class="btn btn-primary" style="margin-top:10px">
                    <i class="fas fa-compass"></i> Khám phá địa điểm
                </a>
            </div>
            <div class="intro-image">
                <img src="./assets/images/img3.jpg"
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

<!-- ===== GALLERY SWIPER ===== -->
<section class="section section-bg gallery-section">
    <div class="container">
        <div class="section-title fade-in">
            <h2>Hình ảnh Vân Hồ</h2>
            <p>Những khoảnh khắc đẹp nhất được ghi lại từ Vân Hồ</p>
        </div>

        <div class="swiper gallery-swiper fade-in">
            <div class="swiper-wrapper">
                <div class="swiper-slide">
                    <img src="./assets/images/img1.jpg"
                        alt="Cảnh đẹp Vân Hồ 1" data-lightbox loading="lazy">
                </div>
                <div class="swiper-slide">
                    <img src="./assets/images/img2.jpg"
                        alt="Cảnh đẹp Vân Hồ 2" data-lightbox loading="lazy">
                </div>
                <div class="swiper-slide">
                    <img src="./assets/images/img3.jpg"
                        alt="Cảnh đẹp Vân Hồ 3" data-lightbox loading="lazy">
                </div>
                <div class="swiper-slide">
                    <img src="https://images.unsplash.com/photo-1470071459604-3b5ec3a7fe05?w=500&h=350&fit=crop"
                        alt="Cảnh đẹp Vân Hồ 4" data-lightbox loading="lazy">
                </div>
                <div class="swiper-slide">
                    <img src="./assets/images/img5.png"
                        alt="Cảnh đẹp Vân Hồ 5" data-lightbox loading="lazy">
                </div>
                <div class="swiper-slide">
                    <img src="https://images.unsplash.com/photo-1441974231531-c6227db76b6e?w=500&h=350&fit=crop"
                        alt="Cảnh đẹp Vân Hồ 6" data-lightbox loading="lazy">
                </div>
                <div class="swiper-slide">
                    <img src="./assets/images/img7.jfif"
                        alt="Cảnh đẹp Vân Hồ 7" data-lightbox loading="lazy">
                </div>
                <div class="swiper-slide">
                    <img src="./assets/images/img8.jpg"
                        alt="Cảnh đẹp Vân Hồ 8" data-lightbox loading="lazy">
                </div>
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