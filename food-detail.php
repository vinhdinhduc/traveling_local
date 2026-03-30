<?php

require_once 'includes/config.php';
require_once __DIR__ . '/functions.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: ' . SITE_URL . '/foods.php');
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM foods WHERE id = ?');
$stmt->execute([$id]);
$food = $stmt->fetch();

if (!$food) {
    header('Location: ' . SITE_URL . '/404.php');
    exit;
}

incrementViews($pdo, 'foods', $id);

$parseLines = static function (?string $value): array {
    if (empty($value)) {
        return [];
    }

    $lines = preg_split('/\r\n|\r|\n/', $value);
    $items = [];
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line !== '') {
            $items[] = $line;
        }
    }
    return $items;
};

$ingredients = $parseLines($food['ingredients'] ?? '');
if (empty($ingredients)) {
    $ingredients = [
        'Nguyên liệu tươi từ núi rừng Tây Bắc',
        'Gia vị truyền thống của người Thái',
        'Lá rừng đặc trưng vùng Vân Hồ',
        'Rau sạch vùng cao không thuốc trừ sâu',
        'Thịt gia súc nuôi thả tự nhiên',
        'Hạt tiêu rừng Sơn La'
    ];
}

$tasteTips = $parseLines($food['taste_tips'] ?? '');
if (empty($tasteTips)) {
    $tasteTips = [
        'Thưởng thức vào buổi sáng sớm khi sương mù còn phủ kín núi để cảm nhận trọn vị.',
        'Kết hợp cùng rượu ngô Vân Hồ hoặc nước lá rừng để cân bằng vị đậm của món ăn.',
        'Đặt tại các nhà hàng hoặc homestay địa phương để được chế biến theo công thức truyền thống chính gốc.',
        'Các phiên chợ phiên cuối tuần tại Vân Hồ là nơi lý tưởng để mua nguyên liệu tươi và thử món này.'
    ];
}

$whereToEatLines = $parseLines($food['where_to_eat'] ?? '');
$whereToEatItems = [];
foreach ($whereToEatLines as $line) {
    $parts = array_map('trim', explode('|', $line, 2));
    $whereToEatItems[] = [
        'name' => $parts[0] ?? '',
        'location' => $parts[1] ?? ''
    ];
}
if (empty($whereToEatItems)) {
    $whereToEatItems = [
        ['name' => 'Chợ phiên Vân Hồ', 'location' => 'T7 & CN'],
        ['name' => 'Nhà hàng Bản Mường', 'location' => 'TT. Vân Hồ'],
        ['name' => 'Homestay Pa Co', 'location' => 'Bản Pa Co']
    ];
}

$foodSubtitle = trim($food['subtitle'] ?? '');
if ($foodSubtitle === '') {
    $foodSubtitle = 'Đặc sản Vân Hồ · Sơn La';
}

$foodOrigin = trim($food['origin'] ?? '');
if ($foodOrigin === '') {
    $foodOrigin = 'Vân Hồ, Sơn La';
}

$foodEthnicity = trim($food['ethnicity'] ?? '');
if ($foodEthnicity === '') {
    $foodEthnicity = 'Thái · Mường';
}

$foodBestSeason = trim($food['best_season'] ?? '');
if ($foodBestSeason === '') {
    $foodBestSeason = 'Tháng 10 - 3';
}

$spiceLevel = isset($food['spice_level']) ? (int)$food['spice_level'] : 2;
$spiceLevel = max(0, min(5, $spiceLevel));

$ratingValue = isset($food['rating_value']) ? (float)$food['rating_value'] : 5;
$ratingValue = max(0, min(5, $ratingValue));
$ratingText = number_format($ratingValue, 1) . '/5';

/* Related foods (same category / recent, exclude current) */
$stmtRelated = $pdo->prepare('SELECT id, name, image, short_description, views FROM foods WHERE id != ? ORDER BY views DESC LIMIT 4');
$stmtRelated->execute([$id]);
$relatedFoods = $stmtRelated->fetchAll();

$imageUrl = !empty($food['image'])
    ? getImageUrl($food['image'], 'foods')
    : 'https://images.unsplash.com/photo-1559847844-5315695dadae?w=1400&h=700&fit=crop';

$pageTitle       = $food['name'];
$pageDescription = excerpt(strip_tags($food['short_description'] ?? $food['description'] ?? ''), 160);
$pageStyles      = ['/assets/css/pages/food-detail.css'];

require_once 'includes/header.php';
?>

<!-- ═══════════════════════════════════════════════
     HERO
════════════════════════════════════════════════ -->
<div class="fdt-hero">
    <img class="fdt-hero__img" src="<?= $imageUrl ?>" alt="<?= sanitize($food['name']) ?>">
    <div class="fdt-hero__overlay"></div>

    <div class="fdt-hero__body">
        <div class="container">
            <nav class="fdt-hero__bc" aria-label="Breadcrumb">
                <a href="<?= SITE_URL ?>"><i class="fas fa-home"></i> Trang chủ</a>
                <span class="sep">/</span>
                <a href="<?= SITE_URL ?>/foods.php">Ẩm thực</a>
                <span class="sep">/</span>
                <span><?= sanitize($food['name']) ?></span>
            </nav>

            <div class="fdt-hero__tag">
                <i class="fas fa-utensils"></i> Đặc sản Vân Hồ
            </div>

            <h1 class="fdt-hero__title"><?= sanitize($food['name']) ?></h1>

            <div class="fdt-hero__meta">
                <span class="fdt-hero__meta-item">
                    <i class="fas fa-eye"></i>
                    <?= number_format((int)$food['views'] + 1) ?> lượt xem
                </span>
                <span class="fdt-hero__meta-item">
                    <i class="fas fa-map-marker-alt"></i>
                    <?= sanitize($foodOrigin) ?>
                </span>
                <span class="fdt-hero__meta-item">
                    <i class="fas fa-leaf"></i>
                    Nguyên liệu địa phương
                </span>
            </div>
        </div>
    </div>
</div>


<!-- ═══════════════════════════════════════════════
     MAIN LAYOUT
════════════════════════════════════════════════ -->
<div style="background:var(--warm-cream,#FFFBF4)">
    <div class="container">
        <div class="fdt-layout">

            <!-- ── MAIN ── -->
            <main class="fdt-main">

                <!-- Câu chuyện món ăn -->
                <section class="fdt-story fade-in">
                    <h2 class="fdt-section-title">
                        <span class="icon-wrap"><i class="fas fa-book-open"></i></span>
                        Câu chuyện ẩm thực
                    </h2>
                    <div class="fdt-story-text">
                        <?php if (!empty($food['description'])): ?>
                            <?= $food['description'] ?>
                        <?php elseif (!empty($food['short_description'])): ?>
                            <p><?= sanitize($food['short_description']) ?></p>
                        <?php else: ?>
                            <p><?= sanitize($food['name']) ?> là một trong những món đặc sản nổi tiếng của vùng Vân Hồ, Sơn La. Được chế biến từ những nguyên liệu tươi ngon của vùng núi Tây Bắc, món ăn này mang trong mình hương vị đặc trưng, không lẫn lộn với bất kỳ nơi nào khác.</p>
                        <?php endif; ?>
                    </div>
                </section>

                <!-- Nguyên liệu chính -->
                <section class="fdt-ingredients fade-in">
                    <h2 class="fdt-section-title">
                        <span class="icon-wrap"><i class="fas fa-seedling"></i></span>
                        Nguyên liệu đặc trưng
                    </h2>
                    <div class="fdt-ing-grid">
                        <?php foreach ($ingredients as $item): ?>
                            <div class="fdt-ing-item"><span class="fdt-ing-dot"></span> <?= sanitize($item) ?></div>
                        <?php endforeach; ?>
                    </div>
                </section>

                <!-- Mẹo thưởng thức -->
                <section class="fdt-tips fade-in">
                    <h2 class="fdt-section-title">
                        <span class="icon-wrap"><i class="fas fa-lightbulb"></i></span>
                        Mẹo thưởng thức
                    </h2>
                    <div class="fdt-tips-list">
                        <?php foreach ($tasteTips as $index => $tip): ?>
                            <div class="fdt-tip-item">
                                <div class="fdt-tip-num"><?= str_pad((string)($index + 1), 2, '0', STR_PAD_LEFT) ?></div>
                                <div class="fdt-tip-text">
                                    <?= sanitize($tip) ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>

                <!-- Related foods -->
                <?php if (count($relatedFoods) > 0): ?>
                    <section class="fdt-related fade-in" style="margin-top:12px">
                        <h2 class="fdt-section-title">
                            <span class="icon-wrap"><i class="fas fa-th-large"></i></span>
                            Món ăn đặc sản khác
                        </h2>
                        <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:16px">
                            <?php foreach ($relatedFoods as $rel): ?>
                                <a href="<?= SITE_URL ?>/food-detail.php?id=<?= $rel['id'] ?>"
                                    style="display:flex;gap:12px;background:#fff;border:1px solid rgba(0,0,0,.07);border-radius:12px;overflow:hidden;padding:12px;transition:box-shadow .2s,transform .2s;text-decoration:none"
                                    onmouseover="this.style.boxShadow='0 6px 20px rgba(0,0,0,.1)';this.style.transform='translateY(-2px)'"
                                    onmouseout="this.style.boxShadow='';this.style.transform=''">
                                    <div style="width:72px;height:72px;border-radius:9px;overflow:hidden;flex-shrink:0">
                                        <img src="<?= !empty($rel['image']) ? getImageUrl($rel['image'], 'foods') : 'https://images.unsplash.com/photo-1565557623262-b51c2513a641?w=100&h=100&fit=crop' ?>"
                                            alt="<?= sanitize($rel['name']) ?>"
                                            style="width:100%;height:100%;object-fit:cover">
                                    </div>
                                    <div style="flex:1;min-width:0">
                                        <div style="font-family:var(--font-heading);font-size:.9rem;font-weight:700;color:#1A1209;margin-bottom:5px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                                            <?= sanitize($rel['name']) ?>
                                        </div>
                                        <div style="font-size:.78rem;color:#8D7B6A;display:-webkit-box;line-clamp:2;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;line-height:1.5">
                                            <?= excerpt($rel['short_description'] ?? '', 60) ?>
                                        </div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endif; ?>

                <!-- Back button -->
                <div style="margin-top:44px">
                    <a href="<?= SITE_URL ?>/foods.php" class="fdt-back-btn">
                        <i class="fas fa-arrow-left"></i> Xem tất cả món đặc sản
                    </a>
                </div>

            </main>

            <!-- ── SIDEBAR ── -->
            <aside class="fdt-sidebar">

                <!-- Info card -->
                <div class="fdt-info-card">
                    <div class="fdt-info-card-top">
                        <div class="fdt-info-card-icon">🍲</div>
                        <div class="fdt-info-card-name"><?= sanitize($food['name']) ?></div>
                        <div class="fdt-info-card-sub"><?= sanitize($foodSubtitle) ?></div>
                    </div>
                    <div class="fdt-info-rows">
                        <div class="fdt-info-row">
                            <span class="fdt-info-row-label"><i class="fas fa-eye"></i> Lượt xem</span>
                            <span class="fdt-info-row-value"><?= number_format((int)$food['views'] + 1) ?></span>
                        </div>
                        <div class="fdt-info-row">
                            <span class="fdt-info-row-label"><i class="fas fa-map-marker-alt"></i> Xuất xứ</span>
                            <span class="fdt-info-row-value"><?= sanitize($foodOrigin) ?></span>
                        </div>
                        <div class="fdt-info-row">
                            <span class="fdt-info-row-label"><i class="fas fa-users"></i> Dân tộc</span>
                            <span class="fdt-info-row-value"><?= sanitize($foodEthnicity) ?></span>
                        </div>
                        <div class="fdt-info-row">
                            <span class="fdt-info-row-label"><i class="fas fa-fire"></i> Độ cay</span>
                            <span class="fdt-info-row-value" style="display:flex;gap:3px;justify-content:flex-end">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="fas fa-pepper-hot" style="color:<?= $i <= $spiceLevel ? '#F57C00' : '#E0E0E0' ?>;font-size:.85rem"></i>
                                <?php endfor; ?>
                            </span>
                        </div>
                        <div class="fdt-info-row">
                            <span class="fdt-info-row-label"><i class="fas fa-clock"></i> Mùa ngon nhất</span>
                            <span class="fdt-info-row-value"><?= sanitize($foodBestSeason) ?></span>
                        </div>
                        <div class="fdt-info-row">
                            <span class="fdt-info-row-label"><i class="fas fa-star"></i> Đánh giá</span>
                            <span class="fdt-info-row-value" style="color:#FFA726">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="<?= $i <= round($ratingValue) ? 'fas' : 'far' ?> fa-star"></i>
                                <?php endfor; ?>
                                <?= sanitize($ratingText) ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Share -->
                <div class="fdt-share-card">
                    <div class="fdt-share-title">Chia sẻ món ăn này</div>
                    <div class="fdt-share-btns">
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode(SITE_URL . '/food-detail.php?id=' . $id) ?>"
                            target="_blank" rel="noopener" class="fdt-share-btn fdt-share-fb">
                            <i class="fab fa-facebook-f"></i> Facebook
                        </a>
                        <a href="https://zalo.me/share/url?url=<?= urlencode(SITE_URL . '/food-detail.php?id=' . $id) ?>"
                            target="_blank" rel="noopener" class="fdt-share-btn fdt-share-zl">
                            <i class="fas fa-comment-dots"></i> Zalo
                        </a>
                        <button class="fdt-share-btn fdt-share-lk" onclick="copyLink(this)"
                            data-url="<?= SITE_URL . '/food-detail.php?id=' . $id ?>">
                            <i class="fas fa-link"></i>
                        </button>
                    </div>
                </div>

                <!-- Where to eat -->
                <div class="fdt-where-card">
                    <div class="fdt-where-title">
                        <i class="fas fa-map-pin" style="color:var(--spice)"></i>
                        Thưởng thức ở đâu?
                    </div>
                    <?php foreach ($whereToEatItems as $place): ?>
                        <div class="fdt-where-item">
                            <span class="fdt-where-dot"></span>
                            <span class="fdt-where-name"><?= sanitize($place['name']) ?></span>
                            <span class="fdt-where-loc"><?= sanitize($place['location']) ?></span>
                        </div>
                    <?php endforeach; ?>
                    <div class="fdt-where-item">
                        <span class="fdt-where-dot" style="background:var(--herb)"></span>
                        <a href="<?= SITE_URL ?>/map.php" style="color:var(--herb);font-weight:600;font-size:.85rem;text-decoration:none">
                            <i class="fas fa-map"></i> Xem bản đồ đầy đủ →
                        </a>
                    </div>
                </div>

            </aside>

        </div>
    </div>
</div>


<!-- ═══════════════════════════════════════════════
     JAVASCRIPT
════════════════════════════════════════════════ -->
<script>
    (function() {
        /* Fade in on scroll */
        var fadeEls = document.querySelectorAll('.fade-in');
        if ('IntersectionObserver' in window) {
            var io = new IntersectionObserver(function(entries) {
                entries.forEach(function(e) {
                    if (e.isIntersecting) {
                        e.target.classList.add('visible');
                        io.unobserve(e.target);
                    }
                });
            }, {
                threshold: 0.1
            });
            fadeEls.forEach(function(el) {
                io.observe(el);
            });
        } else {
            fadeEls.forEach(function(el) {
                el.classList.add('visible');
            });
        }
    })();

    function copyLink(btn) {
        var url = btn.getAttribute('data-url');
        if (navigator.clipboard) {
            navigator.clipboard.writeText(url).then(function() {
                btn.innerHTML = '<i class="fas fa-check"></i>';
                btn.style.background = '#e8f5e9';
                btn.style.color = '#2E7D32';
                btn.style.borderColor = '#4CAF50';
                setTimeout(function() {
                    btn.innerHTML = '<i class="fas fa-link"></i>';
                    btn.style.background = btn.style.color = btn.style.borderColor = '';
                }, 2000);
            });
        }
    }
</script>

<?php require_once 'includes/footer.php'; ?>

