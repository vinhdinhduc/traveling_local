<?php


require_once __DIR__ . '/config.php';
require_once dirname(__DIR__) . '/functions.php';
require_once __DIR__ . '/settings.php';

// Xác định trang hiện tại để active menu
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
$currentUser = getCurrentUser($pdo);
$safePageName = preg_replace('/[^a-z0-9\-]/i', '', $currentPage);
$publicPageCssRel = '/assets/css/pages/' . $safePageName . '.css';
$publicPageCssAbs = dirname(__DIR__) . $publicPageCssRel;

// Lấy cấu hình từ DB
$contactSettings = getSettingsByGroup($pdo, 'contact');
$socialSettings  = getSettingsByGroup($pdo, 'social');
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <!-- SEO Meta Tags -->
    <title><?= isset($pageTitle) ? sanitize($pageTitle) . ' | ' . SITE_NAME : SITE_NAME . ' - Khám phá vẻ đẹp Tây Bắc' ?></title>
    <meta name="description" content="<?= isset($pageDescription) ? sanitize($pageDescription) : SITE_DESCRIPTION ?>">
    <meta name="keywords" content="<?= isset($pageKeywords) ? sanitize($pageKeywords) : SITE_KEYWORDS ?>">
    <meta name="author" content="Du lịch Vân Hồ">

    <!-- Open Graph -->
    <meta property="og:title" content="<?= isset($pageTitle) ? sanitize($pageTitle) : SITE_NAME ?>">
    <meta property="og:description" content="<?= isset($pageDescription) ? sanitize($pageDescription) : SITE_DESCRIPTION ?>">
    <meta property="og:image" content="<?= isset($ogImage) ? $ogImage : SITE_URL . '/assets/images/og-image.jpg' ?>">
    <meta property="og:url" content="<?= SITE_URL . $_SERVER['REQUEST_URI'] ?>">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="vi_VN">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= SITE_URL ?>/assets/images/favicon.png">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <!-- SwiperJS CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Main CSS -->
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">

    <?php if ($safePageName !== '' && file_exists($publicPageCssAbs)): ?>
        <link rel="stylesheet" href="<?= SITE_URL . $publicPageCssRel ?>">
    <?php endif; ?>

    <?php
    if (isset($pageStyles) && is_array($pageStyles)):
        foreach ($pageStyles as $stylePath):
            $stylePath = (string)$stylePath;
            if ($stylePath !== '' && (str_starts_with($stylePath, '/') || preg_match('#^https?://#i', $stylePath))):
                $styleHref = str_starts_with($stylePath, '/') ? (SITE_URL . $stylePath) : $stylePath;
    ?>
                <link rel="stylesheet" href="<?= $styleHref ?>">
    <?php
            endif;
        endforeach;
    endif;
    ?>

    <!-- Schema Markup -->
    <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "TouristAttraction",
            "name": "Du lịch Vân Hồ",
            "description": "<?= SITE_DESCRIPTION ?>",
            "address": {
                "@type": "PostalAddress",
                "addressLocality": "Vân Hồ",
                "addressRegion": "Sơn La",
                "addressCountry": "VN"
            }
        }
    </script>
</head>

<body>

    <!-- Loading Animation -->
    <div class="page-loader">
        <div class="loader-spinner"></div>
    </div>

    <!-- Top Bar -->
    <div class="header-topbar" id="header-topbar">
        <div class="container topbar-inner">
            <div class="topbar-left">
                <span class="topbar-item">
                    <i class="fas fa-map-marker-alt"></i>
                    <?= sanitize($contactSettings['site_address'] ?? 'Vân Hồ, Sơn La') ?>
                </span>
                <span class="topbar-item">
                    <i class="fas fa-phone-alt"></i>
                    <?= sanitize($contactSettings['site_phone'] ?? '') ?>
                </span>
                <span class="topbar-item">
                    <i class="fas fa-clock"></i>
                    <?= sanitize($contactSettings['site_working_hours'] ?? '') ?>
                </span>
            </div>
            <div class="topbar-right">
                <div class="topbar-lang">
                    <i class="fas fa-globe"></i>
                    Tiếng Việt
                </div>
                <div class="topbar-social">
                    <?php if (!empty($socialSettings['facebook_url'])): ?>
                        <a href="<?= sanitize($socialSettings['facebook_url']) ?>" target="_blank" rel="noopener" title="Facebook" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                    <?php endif; ?>
                    <?php if (!empty($socialSettings['zalo_url'])): ?>
                        <a href="<?= sanitize($socialSettings['zalo_url']) ?>" target="_blank" rel="noopener" title="Zalo" aria-label="Zalo"><i class="fas fa-comment-dots"></i></a>
                    <?php endif; ?>
                    <?php if (!empty($socialSettings['youtube_url'])): ?>
                        <a href="<?= sanitize($socialSettings['youtube_url']) ?>" target="_blank" rel="noopener" title="YouTube" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
                    <?php endif; ?>
                    <?php if (!empty($socialSettings['tiktok_url'])): ?>
                        <a href="<?= sanitize($socialSettings['tiktok_url']) ?>" target="_blank" rel="noopener" title="TikTok" aria-label="TikTok"><i class="fab fa-tiktok"></i></a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Header -->
    <header class="header" id="main-header">
        <div class="container header-inner">

            <!-- Logo -->
            <a href="<?= SITE_URL ?>" class="logo">
                <div class="logo-icon-wrap">
                    <i class="fas fa-mountain"></i>
                </div>
                <div class="logo-text">
                    <span class="logo-sub">Du lịch</span>
                    <span class="logo-name">Vân <span>Hồ</span></span>
                </div>
            </a>

            <!-- Navigation -->
            <nav class="nav-menu" id="nav-menu">
                <a href="<?= SITE_URL ?>" class="<?= $currentPage === 'index' ? 'active' : '' ?>">
                    <i class="fas fa-home"></i> Trang chủ
                </a>
                <a href="<?= SITE_URL ?>/places.php" class="<?= $currentPage === 'places' || $currentPage === 'place-detail' ? 'active' : '' ?>">
                    <i class="fas fa-map-marker-alt"></i> Địa điểm
                </a>
                <a href="<?= SITE_URL ?>/foods.php" class="<?= $currentPage === 'foods' || $currentPage === 'food-detail' ? 'active' : '' ?>">
                    <i class="fas fa-utensils"></i> Ẩm thực
                </a>
                <a href="<?= SITE_URL ?>/homestays.php" class="<?= $currentPage === 'homestays' || $currentPage === 'homestay-detail' ? 'active' : '' ?>">
                    <i class="fas fa-house"></i> Homestay
                </a>
                <a href="<?= SITE_URL ?>/map.php" class="<?= $currentPage === 'map' ? 'active' : '' ?>">
                    <i class="fas fa-map"></i> Bản đồ
                </a>
                <a href="<?= SITE_URL ?>/news.php" class="<?= $currentPage === 'news' || $currentPage === 'news-detail' ? 'active' : '' ?>">
                    <i class="fas fa-newspaper"></i> Tin tức
                </a>
                <a href="<?= SITE_URL ?>/contact.php" class="<?= $currentPage === 'contact' ? 'active' : '' ?>">
                    <i class="fas fa-envelope"></i> Liên hệ
                </a>

                <?php if ($currentUser): ?>
                    <a href="<?= SITE_URL ?>/wishlist.php" class="<?= $currentPage === 'wishlist' ? 'active' : '' ?>">
                        <i class="fas fa-heart"></i> Yêu thích
                    </a>
                    <a href="<?= SITE_URL ?>/profile.php" class="<?= $currentPage === 'profile' ? 'active' : '' ?>">
                        <i class="fas fa-user-circle"></i> <?= sanitize($_SESSION['user_name'] ?? 'Tài khoản') ?>
                    </a>
                    <a href="<?= SITE_URL ?>/logout.php" class="nav-logout">
                        <i class="fas fa-sign-out-alt"></i> Đăng xuất
                    </a>
                <?php else: ?>
                    <a href="<?= SITE_URL ?>/login.php" class="nav-login <?= $currentPage === 'login' ? 'active' : '' ?>">
                        <i class="fas fa-right-to-bracket"></i> Đăng nhập
                    </a>
                <?php endif; ?>
            </nav>

            <!-- Hamburger Menu -->
            <button class="hamburger" id="hamburger" aria-label="Mở menu" aria-expanded="false">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
    </header>

    <!-- Mobile Overlay -->
    <div class="mobile-overlay" id="mobile-overlay"></div>
