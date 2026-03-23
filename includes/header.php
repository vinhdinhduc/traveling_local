<?php


require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

// Xác định trang hiện tại để active menu
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
$currentUser = getCurrentUser($pdo);
$safePageName = preg_replace('/[^a-z0-9\-]/i', '', $currentPage);
$publicPageCssRel = '/assets/css/pages/' . $safePageName . '.css';
$publicPageCssAbs = dirname(__DIR__) . $publicPageCssRel;
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

    <!-- Header -->
    <header class="header">
        <div class="container header-inner">
            <!-- Logo -->
            <a href="<?= SITE_URL ?>" class="logo">
                <i class="fas fa-mountain" style="font-size:1.8rem;color:var(--primary)"></i>
                Du lịch <span>Vân Hồ</span>
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
                    <a href="<?= SITE_URL ?>/logout.php">
                        <i class="fas fa-sign-out-alt"></i> Xin chào, <?= sanitize($_SESSION['user_name'] ?? 'Bạn') ?>
                    </a>
                <?php else: ?>
                    <a href="<?= SITE_URL ?>/login.php" class="<?= $currentPage === 'login' ? 'active' : '' ?>">
                        <i class="fas fa-right-to-bracket"></i> Đăng nhập
                    </a>
                <?php endif; ?>
            </nav>

            <!-- Hamburger Menu -->
            <div class="hamburger" id="hamburger">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
    </header>

    <!-- Mobile Overlay -->
    <div class="mobile-overlay" id="mobile-overlay"></div>