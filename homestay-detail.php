<?php

require_once 'includes/config.php';
require_once __DIR__ . '/functions.php';
require_once 'includes/settings.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: ' . SITE_URL . '/homestays.php');
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM homestays WHERE id = ?');
$stmt->execute([$id]);
$homestay = $stmt->fetch();

if (!$homestay) {
    header('Location: ' . SITE_URL . '/404.php');
    exit;
}

incrementViews($pdo, 'homestays', $id);
releaseExpiredPendingBookings($pdo);
$currentUser = getCurrentUser($pdo);
$bookingErrors = [];
$reviewErrors = [];

$checkInValue  = $_POST['check_in']  ?? '';
$checkOutValue = $_POST['check_out'] ?? '';
$guestsValue   = (int)($_POST['guests'] ?? 2);
$noteValue     = trim($_POST['note']  ?? '');

/* ── Review submit ── */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    if (!isUserLoggedIn()) {
        header('Location: ' . SITE_URL . '/login.php?redirect=' . urlencode(SITE_URL . '/homestay-detail.php?id=' . $id));
        exit;
    }
    if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
        $reviewErrors[] = 'Phiên làm việc không hợp lệ.';
    } else {
        $rating  = (int)($_POST['rating']  ?? 0);
        $content = trim($_POST['content'] ?? '');
        if ($rating < 1 || $rating > 5) $reviewErrors[] = 'Điểm đánh giá phải từ 1 đến 5 sao.';
        if (empty($reviewErrors)) {
            $pdo->prepare('INSERT INTO homestay_reviews (homestay_id, user_id, rating, content) VALUES (?, ?, ?, ?)')
                ->execute([$id, (int)$_SESSION['user_id'], $rating, $content]);
            setFlash('success', 'Cảm ơn bạn đã gửi đánh giá homestay!');
            header('Location: ' . SITE_URL . '/homestay-detail.php?id=' . $id);
            exit;
        }
    }
}

/* ── Booking submit ── */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_booking'])) {
    if (!isUserLoggedIn()) {
        header('Location: ' . SITE_URL . '/login.php?redirect=' . urlencode(SITE_URL . '/homestay-detail.php?id=' . $id));
        exit;
    }
    if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
        $bookingErrors[] = 'Phiên làm việc không hợp lệ.';
    } else {
        $checkIn  = $checkInValue;
        $checkOut = $checkOutValue;
        $guests   = max(1, $guestsValue);
        $note     = $noteValue;

        if (empty($checkIn) || empty($checkOut))            $bookingErrors[] = 'Vui lòng chọn ngày nhận/trả phòng.';
        $start = strtotime($checkIn);
        $end   = strtotime($checkOut);
        if ($start === false || $end === false || $end <= $start) $bookingErrors[] = 'Ngày trả phòng phải sau ngày nhận phòng.';
        if ($start !== false && $start < strtotime(date('Y-m-d'))) $bookingErrors[] = 'Ngày nhận phòng phải từ hôm nay trở đi.';

        if (empty($bookingErrors) && !isHomestayAvailable($pdo, $id, $checkIn, $checkOut))
            $bookingErrors[] = 'Khoảng thời gian bạn chọn đã hết phòng. Vui lòng chọn lịch khác.';

        if (empty($bookingErrors)) {
            $nights     = (int)(($end - $start) / 86400);
            $totalPrice = $nights * (float)$homestay['price_per_night'];
            $bookingId  = createPendingBooking($pdo, $id, (int)$_SESSION['user_id'], $checkIn, $checkOut, $guests, $totalPrice, $note);

            // Gửi email xác nhận booking
            $paymentUrl = SITE_URL . '/payment.php?booking_id=' . $bookingId;
            sendTemplateEmail($pdo, 'booking_confirm', $currentUser['email'], [
                'full_name'     => $currentUser['full_name'],
                'booking_id'    => $bookingId,
                'homestay_name' => $homestay['name'],
                'check_in'      => date('d/m/Y', strtotime($checkIn)),
                'check_out'     => date('d/m/Y', strtotime($checkOut)),
                'guests'        => $guests,
                'total_price'   => formatPrice($totalPrice),
                'payment_url'   => $paymentUrl
            ]);

            setFlash('success', 'Đã giữ chỗ trong 10 phút. Vui lòng hoàn tất thanh toán để xác nhận booking.');
            header('Location: ' . $paymentUrl);
            exit;
        }
    }
}

/* ── Stats & Reviews ── */
$reviewStats = $pdo->prepare('SELECT COUNT(*) AS total, COALESCE(AVG(rating), 0) AS avg_rating FROM homestay_reviews WHERE homestay_id = ?');
$reviewStats->execute([$id]);
$reviewStats = $reviewStats->fetch() ?: ['total' => 0, 'avg_rating' => 0];

$stmtReviews = $pdo->prepare('SELECT r.*, u.full_name FROM homestay_reviews r JOIN users u ON u.id = r.user_id WHERE r.homestay_id = ? ORDER BY r.created_at DESC LIMIT 20');
$stmtReviews->execute([$id]);
$reviews = $stmtReviews->fetchAll();

$avgRating   = round((float)$reviewStats['avg_rating'], 1);
$totalReview = (int)$reviewStats['total'];
$imageUrl    = !empty($homestay['image']) ? getImageUrl($homestay['image'], 'homestays') : 'https://images.unsplash.com/photo-1496417263034-38ec4f0b665a?w=1400&h=700&fit=crop';

$maxGuests = isset($homestay['max_guests']) ? (int)$homestay['max_guests'] : 10;
$maxGuests = max(1, $maxGuests);
$checkInTime = trim($homestay['check_in_time'] ?? '');
if ($checkInTime === '') {
    $checkInTime = '14:00';
}
$checkOutTime = trim($homestay['check_out_time'] ?? '');
if ($checkOutTime === '') {
    $checkOutTime = '12:00';
}

$pageTitle       = $homestay['name'];
$pageDescription = excerpt(strip_tags($homestay['short_description'] ?? $homestay['description'] ?? ''), 160);
$pageStyles      = ['/assets/css/pages/homestay-detail.css'];

require_once 'includes/header.php';
?>

<!-- ═══════════════════════════════════════════
     HERO
════════════════════════════════════════════ -->
<div class="hs-hero">
    <img class="hs-hero__img" src="<?= $imageUrl ?>" alt="<?= sanitize($homestay['name']) ?>">
    <div class="hs-hero__overlay"></div>

    <div class="hs-hero__body">
        <div class="container">

            <!-- Breadcrumb -->
            <nav class="hs-hero__breadcrumb" aria-label="Breadcrumb">
                <a href="<?= SITE_URL ?>"><i class="fas fa-home"></i> Trang chủ</a>
                <span class="sep">/</span>
                <a href="<?= SITE_URL ?>/homestays.php">Homestay</a>
                <span class="sep">/</span>
                <span><?= sanitize($homestay['name']) ?></span>
            </nav>

            <div class="hs-hero__tag">
                <i class="fas fa-house"></i> Homestay
            </div>

            <h1 class="hs-hero__title"><?= sanitize($homestay['name']) ?></h1>

            <div class="hs-hero__meta">
                <span class="hs-hero__meta-item">
                    <i class="fas fa-map-marker-alt"></i>
                    <?= sanitize($homestay['address'] ?? 'Vân Hồ, Sơn La') ?>
                </span>
                <?php if ($totalReview > 0): ?>
                    <span class="hs-hero__meta-item">
                        <span class="hs-hero__stars">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="fas fa-star hs-hero__star <?= $i <= round($avgRating) ? '' : 'empty' ?>"></i>
                            <?php endfor; ?>
                        </span>
                        <?= $avgRating ?>/5
                        <span style="opacity:.6">(<?= $totalReview ?> đánh giá)</span>
                    </span>
                <?php endif; ?>
                <span class="hs-hero__meta-item">
                    <i class="fas fa-eye"></i>
                    <?= number_format((int)($homestay['views'] ?? 0)) ?> lượt xem
                </span>
            </div>

        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════
     MAIN LAYOUT
════════════════════════════════════════════ -->
<div class="container">
    <?= getFlash() ?>

    <?php if (!empty($bookingErrors)): ?>
        <div class="hs-alert hs-alert-error" style="margin-top:24px">
            <i class="fas fa-exclamation-circle"></i>
            <div>
                <?php foreach ($bookingErrors as $err): ?>
                    <div><?= sanitize($err) ?></div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="hs-layout">

        <!-- ── MAIN ── -->
        <main class="hs-main">

            <!-- Mô tả -->
            <section class="hs-description fade-in">
                <h2 class="hs-section-title">
                    <i class="fas fa-info-circle"></i>
                    Giới thiệu homestay
                </h2>
                <div class="hs-description-text">
                    <?= $homestay['description'] ?: '<p>' . sanitize($homestay['short_description'] ?? '') . '</p>' ?>
                </div>
            </section>

            <!-- Tiện nghi -->
            <section class="hs-amenities fade-in">
                <h2 class="hs-section-title">
                    <i class="fas fa-concierge-bell"></i>
                    Tiện nghi & Dịch vụ
                </h2>
                <div class="hs-amenities-grid">
                    <?php $amenities = getHomestayAmenities($pdo, $id); ?>
                    <?php foreach ($amenities as $amenity): ?>
                        <div class="hs-amenity-item"><i class="<?= sanitize($amenity['icon']) ?>"></i> <?= sanitize($amenity['name']) ?></div>
                    <?php endforeach; ?>
                </div>
            </section>

            <!-- Đánh giá -->
            <section class="hs-reviews fade-in">
                <h2 class="hs-section-title">
                    <i class="fas fa-star"></i>
                    Đánh giá khách hàng
                </h2>

                <!-- Tổng quan -->
                <?php if ($totalReview > 0): ?>
                    <div class="hs-review-summary">
                        <div class="hs-review-big-score"><?= $avgRating ?></div>
                        <div class="hs-review-big-info">
                            <div class="hs-review-big-stars">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="<?= $i <= round($avgRating) ? 'fas' : 'far' ?> fa-star"></i>
                                <?php endfor; ?>
                            </div>
                            <div class="hs-review-big-count"><?= $totalReview ?> đánh giá từ khách đã lưu trú</div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Form viết đánh giá -->
                <?php if (!empty($reviewErrors)): ?>
                    <div class="hs-alert hs-alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <div><?php foreach ($reviewErrors as $e): ?><div><?= sanitize($e) ?></div><?php endforeach; ?></div>
                    </div>
                <?php endif; ?>

                <?php if ($currentUser): ?>
                    <div class="hs-review-form">
                        <div class="hs-review-form-title">✍️ Viết đánh giá của bạn</div>
                        <form method="POST" action="">
                            <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">

                            <!-- Star picker -->
                            <div class="star-picker" role="group" aria-label="Chọn số sao">
                                <?php for ($i = 5; $i >= 1; $i--): ?>
                                    <input type="radio" name="rating" id="star<?= $i ?>" value="<?= $i ?>">
                                    <label for="star<?= $i ?>" title="<?= $i ?> sao">&#9733;</label>
                                <?php endfor; ?>
                            </div>

                            <textarea name="content" class="form-control" rows="4"
                                placeholder="Chia sẻ trải nghiệm của bạn tại <?= sanitize($homestay['name']) ?>..."></textarea>

                            <button type="submit" name="submit_review" class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i> Gửi đánh giá
                            </button>
                        </form>
                    </div>
                <?php else: ?>
                    <div class="hs-review-form" style="text-align:center;padding:28px">
                        <p style="color:var(--text-light);margin-bottom:14px">
                            <i class="fas fa-lock" style="color:var(--primary);margin-right:6px"></i>
                            Đăng nhập để viết đánh giá của bạn
                        </p>
                        <a href="<?= SITE_URL ?>/login.php?redirect=<?= urlencode(SITE_URL . '/homestay-detail.php?id=' . $id) ?>"
                            class="btn btn-primary"><i class="fas fa-right-to-bracket"></i> Đăng nhập ngay</a>
                    </div>
                <?php endif; ?>

                <!-- Danh sách đánh giá -->
                <div class="hs-review-list">
                    <?php if (count($reviews) > 0): ?>
                        <?php foreach ($reviews as $review): ?>
                            <div class="hs-review-card">
                                <div class="hs-review-card-head">
                                    <div class="hs-review-avatar">
                                        <?= mb_strtoupper(mb_substr($review['full_name'], 0, 1, 'UTF-8'), 'UTF-8') ?>
                                    </div>
                                    <div class="hs-review-info">
                                        <div class="hs-review-name"><?= sanitize($review['full_name']) ?></div>
                                        <div class="hs-review-stars-row">
                                            <span class="stars">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <i class="<?= $i <= (int)$review['rating'] ? 'fas' : 'far' ?> fa-star"></i>
                                                <?php endfor; ?>
                                            </span>
                                            <span class="hs-review-date"><?= formatDateTime($review['created_at']) ?></span>
                                        </div>
                                    </div>
                                </div>
                                <?php if (!empty($review['content'])): ?>
                                    <p class="hs-review-content"><?= nl2br(sanitize($review['content'])) ?></p>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="color:var(--text-muted);text-align:center;padding:28px 0;font-size:.9rem">
                            <i class="far fa-comment-dots" style="font-size:2rem;display:block;margin-bottom:12px;color:var(--border)"></i>
                            Chưa có đánh giá nào. Hãy là người đầu tiên!
                        </p>
                    <?php endif; ?>
                </div>
            </section>

        </main>

        <!-- ── SIDEBAR ── -->
        <aside class="hs-sidebar">
            <div class="hs-price-card">
                <div class="hs-price-card-top">
                    <div class="hs-price-label">Giá mỗi đêm</div>
                    <div class="hs-price-amount"><?= formatPrice((float)$homestay['price_per_night']) ?></div>
                    <div class="hs-price-unit">/ đêm · đã gồm thuế</div>
                </div>

                <div class="hs-price-card-body">
                    <?php if ($totalReview > 0): ?>
                        <div class="hs-price-card-rating">
                            <span class="stars">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="<?= $i <= round($avgRating) ? 'fas' : 'far' ?> fa-star"></i>
                                <?php endfor; ?>
                            </span>
                            <strong><?= $avgRating ?></strong>
                            <span style="color:var(--text-muted)">(<?= $totalReview ?> đánh giá)</span>
                        </div>
                    <?php endif; ?>

                    <div class="hs-info-rows">
                        <div class="hs-info-row">
                            <span class="hs-info-row-label"><i class="fas fa-map-marker-alt"></i> Địa chỉ</span>
                            <span class="hs-info-row-value" style="max-width:160px;text-align:right;font-size:.82rem">
                                <?= sanitize($homestay['address'] ?? 'Vân Hồ, Sơn La') ?>
                            </span>
                        </div>
                        <div class="hs-info-row">
                            <span class="hs-info-row-label"><i class="fas fa-users"></i> Sức chứa</span>
                            <span class="hs-info-row-value"><?= $maxGuests ?> khách</span>
                        </div>
                        <div class="hs-info-row">
                            <span class="hs-info-row-label"><i class="fas fa-clock"></i> Check-in</span>
                            <span class="hs-info-row-value"><?= sanitize($checkInTime) ?></span>
                        </div>
                        <div class="hs-info-row">
                            <span class="hs-info-row-label"><i class="fas fa-clock"></i> Check-out</span>
                            <span class="hs-info-row-value"><?= sanitize($checkOutTime) ?></span>
                        </div>
                    </div>

                    <button class="hs-book-btn" id="openBookingModal">
                        <i class="fas fa-calendar-check"></i> Đặt ngay
                    </button>
                    <p class="hs-book-note">
                        <i class="fas fa-shield-alt"></i> Check-in <?= sanitize($checkInTime) ?> · Check-out <?= sanitize($checkOutTime) ?>
                    </p>

                    <button class="hs-wishlist-btn" id="wishlistBtn">
                        <i class="far fa-heart"></i> Thêm vào yêu thích
                    </button>
                </div>
            </div>
        </aside>

    </div><!-- /.hs-layout -->
</div><!-- /.container -->


<!-- ═══════════════════════════════════════════
     BOOKING MODAL
════════════════════════════════════════════ -->
<div class="hs-modal-backdrop" id="bookingModal" role="dialog" aria-modal="true" aria-label="Đặt homestay">
    <div class="hs-modal" id="bookingModalBox">

        <div class="hs-modal-header">
            <div class="hs-modal-title">
                <i class="fas fa-calendar-check"></i>
                Đặt homestay
            </div>
            <button class="hs-modal-close" id="closeBookingModal" aria-label="Đóng modal">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <!-- Homestay thumb info -->
        <div class="hs-modal-homestay-info">
            <img class="hs-modal-homestay-thumb"
                src="<?= $imageUrl ?>"
                alt="<?= sanitize($homestay['name']) ?>">
            <div>
                <div class="hs-modal-homestay-name"><?= sanitize($homestay['name']) ?></div>
                <div class="hs-modal-homestay-price"><?= formatPrice((float)$homestay['price_per_night']) ?>/đêm</div>
            </div>
        </div>

        <div class="hs-modal-body">

            <?php if ($currentUser): ?>

                <form method="POST" action="" id="bookingForm">
                    <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">

                    <div class="form-row">
                        <div class="form-group">
                            <label for="modal_check_in"><i class="fas fa-sign-in-alt"></i> Ngày nhận phòng</label>
                            <input type="date" name="check_in" id="modal_check_in" class="form-control"
                                required min="<?= date('Y-m-d') ?>"
                                value="<?= sanitize($checkInValue) ?>">
                        </div>
                        <div class="form-group">
                            <label for="modal_check_out"><i class="fas fa-sign-out-alt"></i> Ngày trả phòng</label>
                            <input type="date" name="check_out" id="modal_check_out" class="form-control"
                                required min="<?= date('Y-m-d', strtotime('+1 day')) ?>"
                                value="<?= sanitize($checkOutValue) ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="modal_guests"><i class="fas fa-users"></i> Số khách</label>
                        <input type="number" name="guests" id="modal_guests" class="form-control"
                            min="1" max="<?= $maxGuests ?>"
                            value="<?= $guestsValue ?>">
                    </div>

                    <!-- Price preview -->
                    <div class="hs-modal-price-preview" id="pricePreview">
                        <div class="hs-modal-price-row">
                            <span id="priceNightsLabel">Giá phòng</span>
                            <span id="priceNightsValue">—</span>
                        </div>
                        <div class="hs-modal-price-row total">
                            <span>Tổng cộng</span>
                            <span id="priceTotalValue" style="color:var(--primary)">—</span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="modal_note"><i class="fas fa-pen"></i> Ghi chú (tùy chọn)</label>
                        <textarea name="note" id="modal_note" class="form-control" rows="3"
                            placeholder="Yêu cầu đặc biệt, giờ đến, v.v..."><?= sanitize($noteValue) ?></textarea>
                    </div>

                    <div class="hs-modal-note">
                        <i class="fas fa-clock"></i>
                        Check-in từ <strong><?= sanitize($checkInTime) ?></strong> và check-out trước <strong><?= sanitize($checkOutTime) ?></strong>. Booking sẽ được giữ <strong>10 phút</strong> để hoàn tất thanh toán.
                    </div>

                    <button type="submit" name="submit_booking" class="hs-modal-submit">
                        <i class="fas fa-credit-card"></i> Tiếp tục thanh toán
                    </button>
                </form>

            <?php else: ?>

                <div class="hs-login-prompt">
                    <div class="hs-login-icon">🏡</div>
                    <h3>Đăng nhập để đặt phòng</h3>
                    <p>Bạn cần đăng nhập để đặt homestay và quản lý lịch của mình.</p>
                    <a href="<?= SITE_URL ?>/login.php?redirect=<?= urlencode(SITE_URL . '/homestay-detail.php?id=' . $id) ?>"
                        class="btn">
                        <i class="fas fa-right-to-bracket"></i> Đăng nhập ngay
                    </a>
                </div>

            <?php endif; ?>

        </div>
    </div>
</div>


<!-- ═══════════════════════════════════════════
     JAVASCRIPT
════════════════════════════════════════════ -->
<script>
    (function() {
        'use strict';

        /* ── Modal ── */
        const backdrop = document.getElementById('bookingModal');
        const openBtn = document.getElementById('openBookingModal');
        const closeBtn = document.getElementById('closeBookingModal');
        const modalBox = document.getElementById('bookingModalBox');
        const hadErrors = <?= !empty($bookingErrors) ? 'true' : 'false' ?>;

        function openModal() {
            backdrop.classList.add('open');
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            backdrop.classList.remove('open');
            document.body.style.overflow = '';
        }

        if (openBtn) openBtn.addEventListener('click', openModal);
        if (closeBtn) closeBtn.addEventListener('click', closeModal);

        backdrop && backdrop.addEventListener('click', function(e) {
            if (e.target === backdrop) closeModal();
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && backdrop.classList.contains('open')) closeModal();
        });

        // Auto-open modal if there were booking errors (re-show the form)
        if (hadErrors) openModal();

        /* ── Price calculator ── */
        const pricePerNight = <?= (float)$homestay['price_per_night'] ?>;
        const checkInEl = document.getElementById('modal_check_in');
        const checkOutEl = document.getElementById('modal_check_out');
        const pricePreview = document.getElementById('pricePreview');

        function formatVND(n) {
            return new Intl.NumberFormat('vi-VN', {
                style: 'currency',
                currency: 'VND'
            }).format(n);
        }

        function updatePrice() {
            if (!checkInEl || !checkOutEl) return;
            const start = new Date(checkInEl.value);
            const end = new Date(checkOutEl.value);
            if (!checkInEl.value || !checkOutEl.value || end <= start) {
                pricePreview && pricePreview.classList.remove('visible');
                return;
            }
            const nights = Math.round((end - start) / 86400000);
            const total = nights * pricePerNight;
            document.getElementById('priceNightsLabel').textContent = nights + ' đêm × ' + formatVND(pricePerNight);
            document.getElementById('priceNightsValue').textContent = formatVND(total);
            document.getElementById('priceTotalValue').textContent = formatVND(total);
            pricePreview && pricePreview.classList.add('visible');

            // Auto set check-out min
            checkOutEl.min = new Date(start.getTime() + 86400000).toISOString().split('T')[0];
        }

        checkInEl && checkInEl.addEventListener('change', updatePrice);
        checkOutEl && checkOutEl.addEventListener('change', updatePrice);
        updatePrice();

        /* ── Wishlist toggle ── */
        const wishBtn = document.getElementById('wishlistBtn');
        if (wishBtn) {
            let wishlisted = false;
            wishBtn.addEventListener('click', function() {
                wishlisted = !wishlisted;
                wishBtn.innerHTML = wishlisted ?
                    '<i class="fas fa-heart" style="color:#e53935"></i> Đã thêm vào yêu thích' :
                    '<i class="far fa-heart"></i> Thêm vào yêu thích';
                wishBtn.style.borderColor = wishlisted ? '#e53935' : '';
                wishBtn.style.color = wishlisted ? '#e53935' : '';
                wishBtn.style.background = wishlisted ? 'rgba(229,57,53,.05)' : '';
            });
        }

        /* ── Fade-in on scroll ── */
        const fadeEls = document.querySelectorAll('.fade-in');
        if ('IntersectionObserver' in window) {
            const io = new IntersectionObserver(function(entries) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('visible');
                        io.unobserve(entry.target);
                    }
                });
            }, {
                threshold: 0.12
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
</script>

<?php require_once 'includes/footer.php'; ?>

