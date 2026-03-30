<?php
require_once __DIR__ . '/includes/config.php';

// Trang này đã bị thay thế bởi payment.php cho homestay bookings.
// Script chuyển hướng an toàn:
header('Location: ' . SITE_URL . '/profile.php?tab=bookings');
exit;