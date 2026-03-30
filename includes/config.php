<?php
// config bootstrap


// === CẤU HÌNH DATABASE ===
define('DB_HOST', 'localhost');
define('DB_NAME', 'vanho_tourism');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// === CẤU HÌNH WEBSITE ===
define('SITE_NAME', 'Du lịch Vân Hồ');
define('SITE_DESCRIPTION', 'Khám phá vẻ đẹp thiên nhiên và văn hóa đặc sắc của xã Vân Hồ, tỉnh Sơn La');
define('SITE_KEYWORDS', 'du lịch Vân Hồ, Sơn La, homestay, bản làng, thiên nhiên, văn hóa Mông');
define('SITE_URL', 'http://localhost/vanho-tourism');
define('ADMIN_URL', SITE_URL . '/admin');
define('GOOGLE_MAPS_API_KEY', '');

// === CẤU HÌNH CHUYỂN KHOẢN QR ===
// Thay bằng thông tin tài khoản thực tế của admin/chủ homestay.
define('PAYMENT_QR_BANK_BIN', '970422');
define('PAYMENT_QR_ACCOUNT_NO', '19032003');
define('PAYMENT_QR_ACCOUNT_NAME', 'VAN HO TOURISM');
//aucc umre zwnk vrld

// === ĐƯỜNG DẪN THƯ MỤC ===
define('ROOT_PATH', dirname(__DIR__) . '/');
define('INCLUDES_PATH', ROOT_PATH . 'includes/');
define('UPLOADS_PATH', ROOT_PATH . 'uploads/');
define('UPLOADS_URL', SITE_URL . '/uploads');

// === CẤU HÌNH UPLOAD ===
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'webp', 'jfif']);
define('ITEMS_PER_PAGE', 9);
define('NEWS_PER_PAGE', 6);

// === KẾT NỐI DATABASE VỚI PDO ===
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    die('Lỗi kết nối Database: ' . $e->getMessage());
}

// === KHỞI TẠO SESSION ===
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// === HÀM TẠO CSRF TOKEN ===
function generateCsrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// === HÀM XÁC THỰC CSRF TOKEN ===
function verifyCsrfToken(string $token): bool
{
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
