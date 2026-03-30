<?php


?>
<aside class="admin-sidebar" id="admin-sidebar">
    <div class="sidebar-header">
        <h2><i class="fas fa-mountain"></i> Vân Hồ Admin</h2>
        <p>Quản lý du lịch</p>
    </div>

    <nav class="sidebar-nav">
        <a href="<?= ADMIN_URL ?>/index.php" class="<?= ($adminPage === 'index' && $adminDir === 'admin') ? 'active' : '' ?>">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </a>

        <div class="nav-divider"></div>

        <a href="<?= ADMIN_URL ?>/places/" class="<?= $adminDir === 'places' ? 'active' : '' ?>">
            <i class="fas fa-map-marker-alt"></i> Quản lý địa điểm
        </a>

        <a href="<?= ADMIN_URL ?>/news/" class="<?= $adminDir === 'news' ? 'active' : '' ?>">
            <i class="fas fa-newspaper"></i> Quản lý tin tức
        </a>

        <a href="<?= ADMIN_URL ?>/foods/" class="<?= $adminDir === 'foods' ? 'active' : '' ?>">
            <i class="fas fa-utensils"></i> Quản lý ẩm thực
        </a>

        <a href="<?= ADMIN_URL ?>/homestays/" class="<?= $adminDir === 'homestays' ? 'active' : '' ?>">
            <i class="fas fa-house"></i> Quản lý homestay
        </a>

        <a href="<?= ADMIN_URL ?>/bookings/" class="<?= $adminDir === 'bookings' ? 'active' : '' ?>">
            <i class="fas fa-calendar-check"></i> Quản lý đặt phòng
        </a>

        <a href="<?= ADMIN_URL ?>/users/" class="<?= $adminDir === 'users' ? 'active' : '' ?>">
            <i class="fas fa-users"></i> Quản lý user
        </a>

        <a href="<?= ADMIN_URL ?>/reviews/" class="<?= $adminDir === 'reviews' ? 'active' : '' ?>">
            <i class="fas fa-star"></i> Quản lý đánh giá
        </a>

        <a href="<?= ADMIN_URL ?>/contacts.php" class="<?= $adminPage === 'contacts' ? 'active' : '' ?>">
            <i class="fas fa-envelope"></i> Liên hệ
        </a>

        <div class="nav-divider"></div>
        <div class="nav-heading" style="padding: 10px 20px; font-size: 0.75rem; color: #888; text-transform: uppercase; font-weight: bold;">Hệ thống</div>

        <a href="<?= ADMIN_URL ?>/sliders/" class="<?= $adminDir === 'sliders' ? 'active' : '' ?>">
            <i class="fas fa-images"></i> Quản lý Slider
        </a>

        <a href="<?= ADMIN_URL ?>/gallery/" class="<?= $adminDir === 'gallery' ? 'active' : '' ?>">
            <i class="far fa-images"></i> Quản lý Gallery
        </a>

        <a href="<?= ADMIN_URL ?>/amenities/" class="<?= $adminDir === 'amenities' ? 'active' : '' ?>">
            <i class="fas fa-concierge-bell"></i> Quản lý Tiện nghi
        </a>

        <a href="<?= ADMIN_URL ?>/email-templates/" class="<?= $adminDir === 'email-templates' ? 'active' : '' ?>">
            <i class="fas fa-envelope-open-text"></i> Email Templates
        </a>

        <a href="<?= ADMIN_URL ?>/settings/" class="<?= $adminDir === 'settings' ? 'active' : '' ?>">
            <i class="fas fa-cog"></i> Cài đặt chung
        </a>

        <div class="nav-divider"></div>

        <a href="<?= SITE_URL ?>" target="_blank">
            <i class="fas fa-external-link-alt"></i> Xem website
        </a>

        <a href="<?= ADMIN_URL ?>/logout.php">
            <i class="fas fa-sign-out-alt"></i> Đăng xuất
        </a>
    </nav>
</aside>