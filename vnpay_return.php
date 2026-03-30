<?php

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/functions.php';

setFlash('error', 'Hệ thống đã dừng thanh toán VNPay. Vui lòng thanh toán bằng chuyển khoản QR.');
header('Location: ' . SITE_URL . '/profile.php?tab=bookings');
exit;


