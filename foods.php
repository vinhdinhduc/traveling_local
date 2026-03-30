<?php

$pageTitle       = 'Ẩm thực đặc trưng';
$pageDescription = 'Khám phá các món ăn đặc sản hấp dẫn của xã Vân Hồ, Sơn La';
$pageStyles      = ['/assets/css/pages/foods.css'];

require_once 'includes/header.php';

$currentPageNum = getCurrentPage();
$perPage        = ITEMS_PER_PAGE;
$keyword        = trim($_GET['keyword'] ?? '');
$sort           = $_GET['sort'] ?? 'newest';

$allowedSort = [
    'newest'    => 'created_at DESC',
    'name_asc'  => 'name ASC',
    'name_desc' => 'name DESC',
    'popular'   => 'views DESC, created_at DESC',
];
if (!isset($allowedSort[$sort])) $sort = 'newest';

$whereSql = '';
$params   = [];
if ($keyword !== '') {
    $whereSql       = ' WHERE name LIKE :keyword OR short_description LIKE :keyword OR description LIKE :keyword';
    $params[':keyword'] = '%' . $keyword . '%';
}

$stmtCount = $pdo->prepare('SELECT COUNT(*) FROM foods' . $whereSql);
foreach ($params as $k => $v) $stmtCount->bindValue($k, $v);
$stmtCount->execute();
$totalFoods = (int)$stmtCount->fetchColumn();
$offset     = ($currentPageNum - 1) * $perPage;

$stmt = $pdo->prepare('SELECT * FROM foods' . $whereSql . ' ORDER BY ' . $allowedSort[$sort] . ' LIMIT :limit OFFSET :offset');
foreach ($params as $k => $v) $stmt->bindValue($k, $v);
$stmt->bindValue(':limit',  $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset,  PDO::PARAM_INT);
$stmt->execute();
$foods = $stmt->fetchAll();

$featuredFood   = count($foods) > 0 ? $foods[0]    : null;
$remainingFoods = count($foods) > 1 ? array_slice($foods, 1) : [];
$isSearch       = $keyword !== '' || $sort !== 'newest';
?>

<!-- ═══════════════════════════════════════════════
     HERO
════════════════════════════════════════════════ -->
<section class="fd-hero">
    <div class="fd-hero__bg">
        <div class="fd-hero__bg-cell">
            <img src="https://images.unsplash.com/photo-1559847844-5315695dadae?w=500&h=600&fit=crop" alt="" loading="eager">
        </div>
        <div class="fd-hero__bg-cell">
            <img src="https://images.unsplash.com/photo-1526318896980-cf78c088247c?w=500&h=600&fit=crop" alt="" loading="eager">
        </div>
        <div class="fd-hero__bg-cell">
            <img src="https://images.unsplash.com/photo-1565557623262-b51c2513a641?w=500&h=600&fit=crop" alt="" loading="eager">
        </div>
    </div>
    <div class="fd-hero__overlay"></div>

    <div class="fd-hero__body">
        <div class="container">
            <nav class="fd-hero__bc" aria-label="Breadcrumb">
                <a href="<?= SITE_URL ?>"><i class="fas fa-home"></i> Trang chủ</a>
                <span class="sep">/</span>
                <span>Ẩm thực</span>
            </nav>

            <div class="fd-hero__eyebrow">
                <i class="fas fa-utensils"></i> Đặc sản Tây Bắc
            </div>

            <h1 class="fd-hero__title">
                Hương vị <em>núi rừng</em><br>Vân Hồ, Sơn La
            </h1>

            <p class="fd-hero__desc">
                Những món ăn mang đậm bản sắc văn hoá của đồng bào dân tộc Thái, Mường — được chế biến từ nguyên liệu sạch vùng cao Tây Bắc.
            </p>

            <div class="fd-hero__stats">
                <div class="fd-hero__stat">
                    <span class="fd-hero__stat-num"><?= $totalFoods ?>+</span>
                    <span class="fd-hero__stat-label">Món đặc sản</span>
                </div>
                <div class="fd-hero__stat">
                    <span class="fd-hero__stat-num">100%</span>
                    <span class="fd-hero__stat-label">Nguyên liệu sạch</span>
                </div>
                <div class="fd-hero__stat">
                    <span class="fd-hero__stat-num">3+</span>
                    <span class="fd-hero__stat-label">Dân tộc</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ═══════════════════════════════════════════════
     FILTER BAR (sticky)
════════════════════════════════════════════════ -->
<div class="fd-filter-wrap">
    <div class="container">
        <form method="GET" action="">
            <div class="fd-filter-inner">
                <div class="fd-search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" name="keyword" class="fd-search-input"
                        value="<?= sanitize($keyword) ?>"
                        placeholder="Tìm món ăn: thắng cố, cơm lam, lợn cắp nách...">
                </div>

                <select name="sort" class="fd-sort-select">
                    <option value="newest" <?= $sort === 'newest'    ? 'selected' : '' ?>>🕐 Mới nhất</option>
                    <option value="popular" <?= $sort === 'popular'   ? 'selected' : '' ?>>🔥 Phổ biến nhất</option>
                    <option value="name_asc" <?= $sort === 'name_asc'  ? 'selected' : '' ?>>A → Z</option>
                    <option value="name_desc" <?= $sort === 'name_desc' ? 'selected' : '' ?>>Z → A</option>
                </select>

                <div class="fd-filter-actions" style="display:flex;gap:8px">
                    <button type="submit" class="fd-search-btn">
                        <i class="fas fa-search"></i> Tìm kiếm
                    </button>
                    <?php if ($isSearch): ?>
                        <a href="<?= SITE_URL ?>/foods.php" class="fd-reset-btn">
                            <i class="fas fa-times"></i> Đặt lại
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>
</div>


<!-- ═══════════════════════════════════════════════
     CONTENT
════════════════════════════════════════════════ -->
<div style="background:var(--warm-cream,#FFFBF4);padding-bottom:16px">
    <section class="section" style="padding-top:44px;background:transparent">
        <div class="container">

            <!-- Result bar -->
            <div class="fd-result-bar">
                <p class="fd-result-count">
                    <?php if ($keyword !== ''): ?>
                        Kết quả tìm kiếm "<strong><?= sanitize($keyword) ?></strong>":
                        <strong><?= $totalFoods ?></strong> món ăn
                    <?php else: ?>
                        Hiển thị <strong><?= count($foods) ?></strong>
                        / <strong><?= $totalFoods ?></strong> món ăn đặc sản
                    <?php endif; ?>
                </p>
            </div>

            <?php if (count($foods) > 0): ?>

                <?php if (!$isSearch && $featuredFood): ?>
                    <!-- Featured first item -->
                    <div class="fd-featured fade-in">
                        <a href="<?= SITE_URL ?>/food-detail.php?id=<?= $featuredFood['id'] ?>"
                            class="fd-featured-card" style="text-decoration:none">
                            <div class="fd-featured-img">
                                <?php if (!empty($featuredFood['image'])): ?>
                                    <img src="<?= getImageUrl($featuredFood['image'], 'foods') ?>"
                                        alt="<?= sanitize($featuredFood['name']) ?>">
                                <?php else: ?>
                                    <img src="https://images.unsplash.com/photo-1559847844-5315695dadae?w=700&h=500&fit=crop"
                                        alt="<?= sanitize($featuredFood['name']) ?>">
                                <?php endif; ?>
                                <div class="fd-featured-badge">✦ Đặc sản nổi bật</div>
                                <div class="fd-featured-views">
                                    <i class="fas fa-eye"></i>
                                    <?= number_format((int)$featuredFood['views']) ?> lượt xem
                                </div>
                            </div>
                            <div class="fd-featured-body">
                                <div class="fd-cat-chip">
                                    <i class="fas fa-utensils"></i> Ẩm thực Vân Hồ
                                </div>
                                <h2 class="fd-featured-title"><?= sanitize($featuredFood['name']) ?></h2>
                                <p class="fd-featured-excerpt">
                                    <?= excerpt($featuredFood['short_description'] ?? strip_tags($featuredFood['description'] ?? ''), 220) ?>
                                </p>
                                <div class="fd-read-btn">
                                    Khám phá món ăn này <i class="fas fa-arrow-right"></i>
                                </div>
                            </div>
                        </a>
                    </div>

                    <?php if (count($remainingFoods) > 0): ?>
                        <div class="fd-section-label">
                            <h2>Còn lại <?= count($remainingFoods) ?> món đặc sản</h2>
                        </div>

                        <div class="fd-grid">
                            <?php foreach ($remainingFoods as $idx => $food): ?>
                                <article class="fd-card fade-in" style="animation-delay:<?= $idx * 0.07 ?>s">
                                    <div class="fd-card-img">
                                        <?php if (!empty($food['image'])): ?>
                                            <img src="<?= getImageUrl($food['image'], 'foods') ?>"
                                                alt="<?= sanitize($food['name']) ?>" loading="lazy">
                                        <?php else: ?>
                                            <img src="https://images.unsplash.com/photo-1565557623262-b51c2513a641?w=400&h=280&fit=crop"
                                                alt="<?= sanitize($food['name']) ?>" loading="lazy">
                                        <?php endif; ?>
                                        <div class="fd-card-view">
                                            <i class="fas fa-eye"></i>
                                            <?= number_format((int)$food['views']) ?>
                                        </div>
                                    </div>
                                    <div class="fd-card-body">
                                        <h3 class="fd-card-name">
                                            <a href="<?= SITE_URL ?>/food-detail.php?id=<?= $food['id'] ?>">
                                                <?= sanitize($food['name']) ?>
                                            </a>
                                        </h3>
                                        <p class="fd-card-excerpt">
                                            <?= excerpt($food['short_description'] ?? '', 120) ?>
                                        </p>
                                        <div class="fd-card-footer">
                                            <a href="<?= SITE_URL ?>/food-detail.php?id=<?= $food['id'] ?>"
                                                class="fd-card-detail-btn">
                                                Xem chi tiết <i class="fas fa-arrow-right"></i>
                                            </a>
                                            <span class="fd-card-num">
                                                <i class="fas fa-fire" style="color:var(--spice)"></i>
                                                #<?= $idx + 2 ?>
                                            </span>
                                        </div>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                <?php else: ?>
                    <!-- Search result — plain grid (no featured) -->
                    <div class="fd-section-label">
                        <h2>Kết quả tìm kiếm</h2>
                    </div>
                    <div class="fd-grid">
                        <?php foreach ($foods as $idx => $food): ?>
                            <article class="fd-card fade-in" style="animation-delay:<?= $idx * 0.06 ?>s">
                                <div class="fd-card-img">
                                    <?php if (!empty($food['image'])): ?>
                                        <img src="<?= getImageUrl($food['image'], 'foods') ?>"
                                            alt="<?= sanitize($food['name']) ?>" loading="lazy">
                                    <?php else: ?>
                                        <img src="https://images.unsplash.com/photo-1565557623262-b51c2513a641?w=400&h=280&fit=crop"
                                            alt="<?= sanitize($food['name']) ?>" loading="lazy">
                                    <?php endif; ?>
                                    <div class="fd-card-view">
                                        <i class="fas fa-eye"></i>
                                        <?= number_format((int)$food['views']) ?>
                                    </div>
                                </div>
                                <div class="fd-card-body">
                                    <h3 class="fd-card-name">
                                        <a href="<?= SITE_URL ?>/food-detail.php?id=<?= $food['id'] ?>">
                                            <?= sanitize($food['name']) ?>
                                        </a>
                                    </h3>
                                    <p class="fd-card-excerpt">
                                        <?= excerpt($food['short_description'] ?? '', 120) ?>
                                    </p>
                                    <div class="fd-card-footer">
                                        <a href="<?= SITE_URL ?>/food-detail.php?id=<?= $food['id'] ?>"
                                            class="fd-card-detail-btn">
                                            Xem chi tiết <i class="fas fa-arrow-right"></i>
                                        </a>
                                        <span class="fd-card-num">
                                            <i class="fas fa-eye" style="color:var(--stone)"></i>
                                            <?= number_format((int)$food['views']) ?>
                                        </span>
                                    </div>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- Pagination -->
                <?php $query = buildQueryString(['page' => null]); ?>
                <?= pagination($totalFoods, $perPage, $currentPageNum, SITE_URL . '/foods.php' . ($query !== '' ? ('?' . $query) : '')) ?>

            <?php else: ?>
                <div class="fd-empty fade-in">
                    <div class="fd-empty-icon"><i class="fas fa-utensils"></i></div>
                    <h3>Không tìm thấy món ăn nào</h3>
                    <p>Thử từ khoá khác hoặc xem tất cả món đặc sản của chúng tôi.</p>
                    <a href="<?= SITE_URL ?>/foods.php" class="fd-read-btn" style="margin:20px auto 0">
                        Xem tất cả món ăn <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            <?php endif; ?>

        </div>
    </section>
</div>

<?php require_once 'includes/footer.php'; ?>