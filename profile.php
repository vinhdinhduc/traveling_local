<?php

require_once 'includes/config.php';
require_once __DIR__ . '/functions.php';

requireUserLogin();
releaseExpiredPendingBookings($pdo);

$user = getCurrentUser($pdo);
if (!$user) {
    header('Location: ' . SITE_URL . '/login.php');
    exit;
}

$tab = $_GET['tab'] ?? 'overview';
if (!in_array($tab, ['overview', 'bookings', 'wishlist', 'edit'], true)) {
    $tab = 'overview';
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
        $errors[] = 'Phiên làm việc không hợp lệ.';
    } else {
        $fullName = trim($_POST['full_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');

        if (mb_strlen($fullName) < 2) {
            $errors[] = 'Họ tên phải có ít nhất 2 ký tự.';
        }

        if (empty($errors)) {
            $stmtUpdate = $pdo->prepare('UPDATE users SET full_name = ?, phone = ? WHERE id = ?');
            $stmtUpdate->execute([$fullName, $phone, (int)$user['id']]);
            $_SESSION['user_name'] = $fullName;
            setFlash('success', 'Cập nhật thông tin cá nhân thành công.');
            header('Location: ' . SITE_URL . '/profile.php?tab=edit');
            exit;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_booking'])) {
    if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
        setFlash('error', 'Phiên làm việc không hợp lệ.');
    } else {
        $cancelId = (int)($_POST['booking_id'] ?? 0);
        $stmtCheck = $pdo->prepare('SELECT id, status, check_in FROM homestay_bookings WHERE id = ? AND user_id = ? LIMIT 1');
        $stmtCheck->execute([$cancelId, (int)$user['id']]);
        $bookingCanCancel = $stmtCheck->fetch();

        if (!$bookingCanCancel) {
            setFlash('error', 'Không tìm thấy booking để hủy.');
        } elseif ($bookingCanCancel['status'] === 'cancelled') {
            setFlash('error', 'Booking đã ở trạng thái hủy.');
        } elseif (strtotime($bookingCanCancel['check_in']) <= strtotime(date('Y-m-d'))) {
            setFlash('error', 'Không thể hủy booking trong ngày nhận phòng hoặc sau đó.');
        } else {
            $pdo->prepare('UPDATE homestay_bookings SET status = "cancelled", updated_at = NOW() WHERE id = ?')->execute([$cancelId]);
            setFlash('success', 'Đã hủy booking thành công.');
        }
    }
    header('Location: ' . SITE_URL . '/profile.php?tab=bookings');
    exit;
}

$stmtCounts = $pdo->prepare('SELECT
    COUNT(*) AS total_booking,
    SUM(CASE WHEN status = "confirmed" THEN 1 ELSE 0 END) AS confirmed_booking,
    SUM(CASE WHEN status = "pending" THEN 1 ELSE 0 END) AS pending_booking,
    SUM(CASE WHEN status = "cancelled" THEN 1 ELSE 0 END) AS cancelled_booking
    FROM homestay_bookings
    WHERE user_id = ?');
$stmtCounts->execute([(int)$user['id']]);
$counts = $stmtCounts->fetch() ?: ['total_booking' => 0, 'confirmed_booking' => 0, 'pending_booking' => 0, 'cancelled_booking' => 0];

$stmtBookings = $pdo->prepare('SELECT b.*, h.name AS homestay_name,
    (SELECT p.payment_method FROM payments p WHERE p.booking_id = b.id ORDER BY p.id DESC LIMIT 1) AS payment_method,
    (SELECT p.status FROM payments p WHERE p.booking_id = b.id ORDER BY p.id DESC LIMIT 1) AS latest_payment_state
    FROM homestay_bookings b
    JOIN homestays h ON h.id = b.homestay_id
    WHERE b.user_id = ?
    ORDER BY b.created_at DESC');
$stmtBookings->execute([(int)$user['id']]);
$bookings = $stmtBookings->fetchAll();

$stmtWishlist = $pdo->prepare('SELECT w.created_at, p.id, p.name, p.short_description, p.image, p.location
    FROM wishlists w
    JOIN places p ON p.id = w.place_id
    WHERE w.user_id = ?
    ORDER BY w.created_at DESC');
$stmtWishlist->execute([(int)$user['id']]);
$wishlists = $stmtWishlist->fetchAll();

$stmtUserDetail = $pdo->prepare('SELECT full_name, email, phone, created_at FROM users WHERE id = ? LIMIT 1');
$stmtUserDetail->execute([(int)$user['id']]);
$userDetail = $stmtUserDetail->fetch() ?: $user;

$pageTitle = 'Quản lý cá nhân';
$pageDescription = 'Thông tin cá nhân và lịch sử đặt homestay';
require_once 'includes/header.php';

// Enqueue profile CSS (adjust path to match your project structure)
echo '<link rel="stylesheet" href="' . SITE_URL . '/assets/css/profile-style.css">';
?>

<div class="profile-page">

    <!-- ══════════════ BREADCRUMB ══════════════ -->
    <nav class="profile-breadcrumb">
        <div class="container">
            <ul>
                <li><a href="<?= SITE_URL ?>"><i class="fas fa-home"></i> Trang chủ</a></li>
                <li class="separator">/</li>
                <li class="current">Quản lý cá nhân</li>
            </ul>
        </div>
    </nav>

    <!-- ══════════════ HERO BANNER ══════════════ -->
    <div class="profile-hero">
        <div class="profile-hero-pattern"></div>

        <div class="profile-hero-inner">
            <div class="profile-hero-greeting">
                <i class="fas fa-compass"></i>
                Hành trình của bạn
            </div>
            <h1 class="profile-hero-name">
                Xin chào, <?= sanitize($userDetail['full_name']) ?> ✦
            </h1>
            <div class="profile-hero-meta">
                <span class="profile-hero-badge">
                    <i class="fas fa-envelope"></i>
                    <?= sanitize($userDetail['email'] ?? '') ?>
                </span>
                <span class="profile-hero-badge">
                    <i class="fas fa-calendar-alt"></i>
                    Tham gia <?= date('m/Y', strtotime($userDetail['created_at'] ?? 'now')) ?>
                </span>
                <span class="profile-hero-badge">
                    <i class="fas fa-map-marked-alt"></i>
                    <?= (int)$counts['total_booking'] ?> chuyến đi
                </span>
            </div>
        </div>

        <!-- Wave SVG -->
        <div class="profile-hero-waves">
            <svg viewBox="0 0 1440 60" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M0,30 C360,60 1080,0 1440,30 L1440,60 L0,60 Z" fill="#f8f3ea" />
            </svg>
        </div>
    </div>

    <!-- ══════════════ MAIN CONTENT ══════════════ -->
    <div class="profile-main">

        <!-- Flash Messages -->
        <?php $flash = getFlash();
        if ($flash): ?>
            <div class="profile-flash <?= strpos($flash, 'thành công') !== false ? 'success' : 'error' ?>">
                <i class="fas <?= strpos($flash, 'thành công') !== false ? 'fa-check-circle' : 'fa-exclamation-circle' ?>"></i>
                <?= $flash ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="profile-errors">
                <?php foreach ($errors as $err): ?>
                    <div><i class="fas fa-exclamation-triangle"></i> <?= sanitize($err) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Navigation Tabs -->
        <nav class="profile-nav-card">
            <a class="profile-nav-tab <?= $tab === 'overview'  ? 'active' : '' ?>"
                href="<?= SITE_URL ?>/profile.php?tab=overview">
                <i class="fas fa-chart-pie"></i> <span>Tổng quan</span>
            </a>
            <a class="profile-nav-tab <?= $tab === 'bookings'  ? 'active' : '' ?>"
                href="<?= SITE_URL ?>/profile.php?tab=bookings">
                <i class="fas fa-bed"></i> <span>Booking</span>
            </a>
            <a class="profile-nav-tab <?= $tab === 'wishlist'  ? 'active' : '' ?>"
                href="<?= SITE_URL ?>/profile.php?tab=wishlist">
                <i class="fas fa-heart"></i> <span>Yêu thích</span>
            </a>
            <a class="profile-nav-tab <?= $tab === 'edit'      ? 'active' : '' ?>"
                href="<?= SITE_URL ?>/profile.php?tab=edit">
                <i class="fas fa-user-edit"></i> <span>Thông tin</span>
            </a>
        </nav>

        <!-- ══════ TAB: OVERVIEW ══════ -->
        <?php if ($tab === 'overview'): ?>

            <div class="profile-section-header">
                <div class="profile-section-icon"><i class="fas fa-chart-pie"></i></div>
                <h2 class="profile-section-title">Tổng <span>quan</span></h2>
            </div>
            <div class="profile-ornament"><span class="profile-ornament-icon">✦</span></div>

            <div class="profile-stats-grid">
                <div class="profile-stat-card total">
                    <i class="fas fa-suitcase-rolling profile-stat-card-bg-icon"></i>
                    <div class="profile-stat-label"><i class="fas fa-suitcase-rolling"></i> Tổng chuyến đi</div>
                    <div class="profile-stat-number"><?= (int)$counts['total_booking'] ?></div>
                    <div class="profile-stat-sub">Toàn bộ lịch sử booking</div>
                </div>
                <div class="profile-stat-card confirmed">
                    <i class="fas fa-check-circle profile-stat-card-bg-icon"></i>
                    <div class="profile-stat-label"><i class="fas fa-check-circle"></i> Đã xác nhận</div>
                    <div class="profile-stat-number"><?= (int)$counts['confirmed_booking'] ?></div>
                    <div class="profile-stat-sub">Chuyến đi được duyệt</div>
                </div>
                <div class="profile-stat-card pending">
                    <i class="fas fa-clock profile-stat-card-bg-icon"></i>
                    <div class="profile-stat-label"><i class="fas fa-clock"></i> Đang chờ</div>
                    <div class="profile-stat-number"><?= (int)$counts['pending_booking'] ?></div>
                    <div class="profile-stat-sub">Chờ xử lý / thanh toán</div>
                </div>
                <div class="profile-stat-card cancelled">
                    <i class="fas fa-times-circle profile-stat-card-bg-icon"></i>
                    <div class="profile-stat-label"><i class="fas fa-times-circle"></i> Đã hủy</div>
                    <div class="profile-stat-number"><?= (int)$counts['cancelled_booking'] ?></div>
                    <div class="profile-stat-sub">Booking đã bị hủy</div>
                </div>
            </div>

            <div class="profile-welcome-panel">
                <h3>🏔 Khám phá Vân Hồ cùng chúng tôi</h3>
                <p>Cảm ơn bạn đã đồng hành! Mỗi chuyến đi là một kỷ niệm đáng nhớ. Hãy tiếp tục khám phá những homestay tuyệt vời tại vùng đất Vân Hồ xinh đẹp.</p>
            </div>

        <?php endif; ?>

        <!-- ══════ TAB: BOOKINGS ══════ -->
        <?php if ($tab === 'bookings'): ?>

            <div class="profile-section-header">
                <div class="profile-section-icon"><i class="fas fa-bed"></i></div>
                <h2 class="profile-section-title">Lịch sử <span>Booking</span></h2>
            </div>
            <div class="profile-ornament"><span class="profile-ornament-icon">✦</span></div>

            <div class="profile-table-card">
                <div class="profile-table-wrap">
                    <table class="profile-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th><i class="fas fa-home"></i> Homestay</th>
                                <th><i class="fas fa-calendar-alt"></i> Lịch trình</th>
                                <th><i class="fas fa-tag"></i> Tổng tiền</th>
                                <th><i class="fas fa-credit-card"></i> Thanh toán</th>
                                <th><i class="fas fa-info-circle"></i> Trạng thái</th>
                                <th><i class="fas fa-cog"></i> Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($bookings) > 0): ?>
                                <?php foreach ($bookings as $booking): ?>
                                    <tr>
                                        <td>
                                            <span class="profile-booking-id">#<?= (int)$booking['id'] ?></span>
                                        </td>
                                        <td>
                                            <span class="profile-booking-name"><?= sanitize($booking['homestay_name']) ?></span>
                                        </td>
                                        <td>
                                            <div class="profile-date-range"><?= sanitize($booking['check_in']) ?></div>
                                            <div class="profile-date-sep">→ <?= sanitize($booking['check_out']) ?></div>
                                            <div class="profile-booking-guests">
                                                <i class="fas fa-user-friends"></i>
                                                <?= (int)$booking['guests'] ?> khách
                                            </div>
                                        </td>
                                        <td>
                                            <span class="profile-price"><?= formatPrice((float)$booking['total_price']) ?></span>
                                        </td>
                                        <td>
                                            <?php if ($booking['payment_status'] === 'paid'): ?>
                                                <span class="pf-badge pf-badge-success">
                                                    <i class="fas fa-check-circle"></i> Đã thanh toán
                                                </span>
                                            <?php elseif (($booking['payment_method'] ?? '') === 'BANK_QR' && ($booking['latest_payment_state'] ?? '') === 'pending'): ?>
                                                <span class="pf-badge pf-badge-warning">
                                                    <i class="fas fa-hourglass-half"></i> Chờ admin xác nhận CK QR
                                                </span>
                                            <?php else: ?>
                                                <span class="pf-badge pf-badge-warning">
                                                    <i class="fas fa-clock"></i> Chưa thanh toán
                                                </span>
                                            <?php endif; ?>
                                            <?php if (!empty($booking['payment_method'])): ?>
                                                <div class="profile-payment-method">
                                                    <i class="fas fa-credit-card"></i>
                                                    <?= sanitize($booking['payment_method'] === 'BANK_QR' ? 'Chuyển khoản QR' : $booking['payment_method']) ?>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($booking['status'] === 'confirmed'): ?>
                                                <span class="pf-badge pf-badge-success">
                                                    <i class="fas fa-check"></i> Xác nhận
                                                </span>
                                            <?php elseif ($booking['status'] === 'cancelled'): ?>
                                                <span class="pf-badge pf-badge-danger">
                                                    <i class="fas fa-times"></i> Đã hủy
                                                </span>
                                            <?php else: ?>
                                                <span class="pf-badge pf-badge-warning">
                                                    <i class="fas fa-hourglass-half"></i> Chờ xử lý
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="profile-action-group">
                                                <?php if ($booking['status'] === 'pending' && $booking['payment_status'] === 'unpaid' && (
                                                    (!empty($booking['hold_until']) && strtotime($booking['hold_until']) >= time())
                                                    || (($booking['payment_method'] ?? '') === 'BANK_QR' && ($booking['latest_payment_state'] ?? '') === 'pending')
                                                )): ?>
                                                    <a href="<?= SITE_URL ?>/payment.php?booking_id=<?= (int)$booking['id'] ?>"
                                                        class="profile-btn-pay">
                                                        <i class="fas fa-wallet"></i>
                                                        <?= (($booking['payment_method'] ?? '') === 'BANK_QR' && ($booking['latest_payment_state'] ?? '') === 'pending') ? 'Xem trạng thái thanh toán' : 'Thanh toán' ?>
                                                    </a>
                                                <?php endif; ?>
                                                <?php if ($booking['status'] !== 'cancelled' && strtotime($booking['check_in']) > strtotime(date('Y-m-d'))): ?>
                                                    <form method="POST" action=""
                                                        onsubmit="return confirm('Bạn chắc chắn muốn hủy booking này?')">
                                                        <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                                                        <input type="hidden" name="booking_id" value="<?= (int)$booking['id'] ?>">
                                                        <button type="submit" name="cancel_booking" class="profile-btn-cancel">
                                                            <i class="fas fa-ban"></i> Hủy
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7">
                                        <div class="profile-empty">
                                            <i class="fas fa-bed"></i>
                                            <p>Bạn chưa có booking nào.</p>
                                            <a href="<?= SITE_URL ?>/homestays.php">
                                                <i class="fas fa-search"></i> Khám phá homestay
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        <?php endif; ?>

        <!-- ══════ TAB: WISHLIST ══════ -->
        <?php if ($tab === 'wishlist'): ?>

            <div class="profile-section-header">
                <div class="profile-section-icon"><i class="fas fa-heart"></i></div>
                <h2 class="profile-section-title">Địa điểm <span>yêu thích</span></h2>
            </div>
            <div class="profile-ornament"><span class="profile-ornament-icon">✦</span></div>

            <?php if (count($wishlists) > 0): ?>
                <div class="profile-wishlist-grid">
                    <?php foreach ($wishlists as $item): ?>
                        <div class="profile-wish-card">
                            <div class="profile-wish-img-wrap">
                                <?php if (!empty($item['image'])): ?>
                                    <img src="<?= getImageUrl($item['image'], 'places') ?>"
                                        alt="<?= sanitize($item['name']) ?>">
                                <?php else: ?>
                                    <img src="https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=600&h=400&fit=crop"
                                        alt="<?= sanitize($item['name']) ?>">
                                <?php endif; ?>
                                <div class="profile-wish-img-overlay"></div>
                                <div class="profile-wish-location-tag">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <?= sanitize($item['location'] ?? 'Vân Hồ') ?>
                                </div>
                                <div class="profile-wish-heart">
                                    <i class="fas fa-heart"></i>
                                </div>
                            </div>
                            <div class="profile-wish-body">
                                <h3 class="profile-wish-name"><?= sanitize($item['name']) ?></h3>
                                <p class="profile-wish-desc"><?= excerpt($item['short_description'] ?? '', 100) ?></p>
                                <a href="<?= SITE_URL ?>/place-detail.php?id=<?= (int)$item['id'] ?>"
                                    class="profile-wish-link">
                                    Khám phá ngay <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="profile-table-card">
                    <div class="profile-empty">
                        <i class="fas fa-heart"></i>
                        <p>Danh sách yêu thích đang trống.<br>Hãy khám phá và lưu những địa điểm bạn thích!</p>
                        <a href="<?= SITE_URL ?>/places.php">
                            <i class="fas fa-map-marked-alt"></i> Khám phá địa điểm
                        </a>
                    </div>
                </div>
            <?php endif; ?>

        <?php endif; ?>

        <!-- ══════ TAB: EDIT PROFILE ══════ -->
        <?php if ($tab === 'edit'): ?>

            <div class="profile-section-header">
                <div class="profile-section-icon"><i class="fas fa-user-edit"></i></div>
                <h2 class="profile-section-title">Thông tin <span>cá nhân</span></h2>
            </div>
            <div class="profile-ornament"><span class="profile-ornament-icon">✦</span></div>

            <div class="profile-edit-wrap">

                <!-- Sidebar -->
                <div class="profile-edit-sidebar">
                    <div class="profile-edit-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="profile-edit-sidebar-name"><?= sanitize($userDetail['full_name']) ?></div>
                    <div class="profile-edit-sidebar-email"><?= sanitize($userDetail['email'] ?? '') ?></div>
                    <?php if (!empty($userDetail['phone'])): ?>
                        <div class="profile-edit-sidebar-email" style="margin-top:6px">
                            <i class="fas fa-phone" style="font-size:11px"></i>
                            <?= sanitize($userDetail['phone']) ?>
                        </div>
                    <?php endif; ?>
                    <div class="profile-edit-sidebar-since">
                        <i class="fas fa-calendar-check"></i>
                        Tham gia từ <?= formatDate($userDetail['created_at'] ?? date('Y-m-d')) ?>
                    </div>
                </div>

                <!-- Form -->
                <div class="profile-edit-form-card">
                    <h3 class="profile-form-title">Cập nhật thông tin</h3>
                    <p class="profile-form-subtitle">Hãy đảm bảo thông tin của bạn luôn chính xác để chúng tôi phục vụ tốt hơn.</p>

                    <form method="POST" action="">
                        <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">

                        <div class="profile-field">
                            <label for="full_name">Họ và tên</label>
                            <div class="profile-input-wrap">
                                <i class="fas fa-user"></i>
                                <input type="text" id="full_name" name="full_name"
                                    required
                                    value="<?= sanitize($userDetail['full_name'] ?? '') ?>"
                                    placeholder="Nhập họ và tên đầy đủ">
                            </div>
                        </div>

                        <div class="profile-field">
                            <label for="email">Email</label>
                            <div class="profile-input-wrap">
                                <i class="fas fa-envelope"></i>
                                <input type="email" id="email" disabled
                                    value="<?= sanitize($userDetail['email'] ?? '') ?>">
                            </div>
                            <div class="profile-input-disabled-hint">
                                <i class="fas fa-lock" style="font-size:10px"></i>
                                Email không thể thay đổi
                            </div>
                        </div>

                        <div class="profile-field">
                            <label for="phone">Số điện thoại</label>
                            <div class="profile-input-wrap">
                                <i class="fas fa-phone"></i>
                                <input type="text" id="phone" name="phone"
                                    value="<?= sanitize($userDetail['phone'] ?? '') ?>"
                                    placeholder="Nhập số điện thoại">
                            </div>
                        </div>

                        <div class="profile-field">
                            <label>Ngày tham gia</label>
                            <div class="profile-input-wrap">
                                <i class="fas fa-calendar-alt"></i>
                                <input type="text" disabled
                                    value="<?= formatDate($userDetail['created_at'] ?? date('Y-m-d')) ?>">
                            </div>
                        </div>

                        <div class="profile-form-divider"></div>

                        <button type="submit" name="update_profile" class="profile-btn-save">
                            <i class="fas fa-save"></i> Lưu thông tin
                        </button>
                    </form>
                </div>

            </div>

        <?php endif; ?>

    </div><!-- /.profile-main -->
</div><!-- /.profile-page -->

<?php require_once 'includes/footer.php'; ?>

