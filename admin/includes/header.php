<?php


require_once dirname(dirname(__DIR__)) . '/includes/config.php';
require_once dirname(dirname(__DIR__)) . '/includes/functions.php';

// Kiểm tra đăng nhập
requireLogin();

// Lấy trang admin hiện tại
$adminPage = basename($_SERVER['PHP_SELF'], '.php');
$adminDir = basename(dirname($_SERVER['PHP_SELF']));
$adminKey = ($adminDir === 'admin') ? $adminPage : ($adminDir . '-' . $adminPage);
$safeAdminKey = preg_replace('/[^a-z0-9\-]/i', '', $adminKey);
$adminPageCssRel = '/assets/css/admin-pages/' . $safeAdminKey . '.css';
$adminPageCssAbs = dirname(dirname(__DIR__)) . $adminPageCssRel;
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($adminTitle) ? sanitize($adminTitle) . ' | ' : '' ?>Admin - Du lịch Vân Hồ</title>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Admin CSS -->
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/admin.css">

    <?php if ($safeAdminKey !== '' && file_exists($adminPageCssAbs)): ?>
        <link rel="stylesheet" href="<?= SITE_URL . $adminPageCssRel ?>">
    <?php endif; ?>

    <?php
    if (isset($adminStyles) && is_array($adminStyles)):
        foreach ($adminStyles as $stylePath):
            $stylePath = (string)$stylePath;
            if ($stylePath !== '' && str_starts_with($stylePath, '/')):
    ?>
                <link rel="stylesheet" href="<?= SITE_URL . $stylePath ?>">
    <?php
            endif;
        endforeach;
    endif;
    ?>
</head>

<body>

    <div class="admin-wrapper">
        <!-- Sidebar -->
        <?php include __DIR__ . '/sidebar.php'; ?>

        <!-- Main Content -->
        <div class="admin-main">
            <!-- Top Bar -->
            <div class="admin-topbar">
                <div class="topbar-left">
                    <button class="sidebar-toggle" id="sidebar-toggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <span class="topbar-title"><?= isset($adminTitle) ? sanitize($adminTitle) : 'Dashboard' ?></span>
                </div>
                <div class="topbar-right">
                    <span class="admin-name">
                        <i class="fas fa-user-circle"></i>
                        <?= sanitize($_SESSION['admin_username'] ?? 'Admin') ?>
                    </span>
                    <a href="<?= ADMIN_URL ?>/logout.php" class="btn-logout">
                        <i class="fas fa-sign-out-alt"></i> Đăng xuất
                    </a>
                </div>
            </div>

            <!-- Content -->
            <div class="admin-content">
                <?= getFlash() ?>