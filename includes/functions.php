<?php



/**
 * Làm sạch dữ liệu đầu vào
 */
function sanitize(string $data): string
{
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Tạo slug từ tiếng Việt
 */
function createSlug(string $str): string
{
    $unicode = [
        'a' => 'á|à|ả|ã|ạ|ă|ắ|ằ|ẳ|ẵ|ặ|â|ấ|ầ|ẩ|ẫ|ậ',
        'd' => 'đ',
        'e' => 'é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ',
        'i' => 'í|ì|ỉ|ĩ|ị',
        'o' => 'ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ',
        'u' => 'ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự',
        'y' => 'ý|ỳ|ỷ|ỹ|ỵ',
        'A' => 'Á|À|Ả|Ã|Ạ|Ă|Ắ|Ằ|Ẳ|Ẵ|Ặ|Â|Ấ|Ầ|Ẩ|Ẫ|Ậ',
        'D' => 'Đ',
        'E' => 'É|È|Ẻ|Ẽ|Ẹ|Ê|Ế|Ề|Ể|Ễ|Ệ',
        'I' => 'Í|Ì|Ỉ|Ĩ|Ị',
        'O' => 'Ó|Ò|Ỏ|Õ|Ọ|Ô|Ố|Ồ|Ổ|Ỗ|Ộ|Ơ|Ớ|Ờ|Ở|Ỡ|Ợ',
        'U' => 'Ú|Ù|Ủ|Ũ|Ụ|Ư|Ứ|Ừ|Ử|Ữ|Ự',
        'Y' => 'Ý|Ỳ|Ỷ|Ỹ|Ỵ',
    ];
    foreach ($unicode as $nonUnicode => $uni) {
        $str = preg_replace("/($uni)/i", $nonUnicode, $str);
    }
    $str = strtolower($str);
    $str = preg_replace('/[^a-z0-9\s-]/', '', $str);
    $str = preg_replace('/[\s-]+/', '-', $str);
    return trim($str, '-');
}

/**
 * Upload ảnh với kiểm tra bảo mật
 */
function uploadImage(array $file, string $folder = 'places'): string|false
{
    // Kiểm tra lỗi upload
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }

    // Kiểm tra kích thước file
    if ($file['size'] > MAX_FILE_SIZE) {
        return false;
    }

    // Kiểm tra định dạng file
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, ALLOWED_EXTENSIONS)) {
        return false;
    }

    // Kiểm tra MIME type
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);
    $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($mimeType, $allowedMimes)) {
        return false;
    }

    // Tạo tên file mới (tránh trùng lặp)
    $newName = time() . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
    $uploadDir = UPLOADS_PATH . $folder . '/';

    // Tạo thư mục nếu chưa tồn tại
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $uploadPath = $uploadDir . $newName;

    // Di chuyển file
    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        return $newName;
    }

    return false;
}


function deleteImage(string $filename, string $folder = 'places'): bool
{
    $filePath = UPLOADS_PATH . $folder . '/' . $filename;
    if (file_exists($filePath)) {
        return unlink($filePath);
    }
    return false;
}

/**
 * Lấy URL đầy đủ của ảnh
 */
function getImageUrl(string $filename, string $folder = 'places'): string
{
    if (empty($filename)) {
        return SITE_URL . '/assets/images/no-image.jpg';
    }
    return UPLOADS_URL . '/' . $folder . '/' . $filename;
}

/**
 * Phân trang - Tạo HTML pagination
 */
function pagination(int $totalItems, int $perPage, int $currentPage, string $baseUrl): string
{
    $totalPages = ceil($totalItems / $perPage);
    if ($totalPages <= 1) return '';

    $html = '<nav class="pagination-wrapper"><ul class="pagination">';

    // Nút Previous
    if ($currentPage > 1) {
        $html .= '<li><a href="' . $baseUrl . '?page=' . ($currentPage - 1) . '" class="page-link">&laquo; Trước</a></li>';
    }

    // Các trang
    $start = max(1, $currentPage - 2);
    $end = min($totalPages, $currentPage + 2);

    if ($start > 1) {
        $html .= '<li><a href="' . $baseUrl . '?page=1" class="page-link">1</a></li>';
        if ($start > 2) $html .= '<li><span class="page-dots">...</span></li>';
    }

    for ($i = $start; $i <= $end; $i++) {
        $active = ($i === $currentPage) ? ' active' : '';
        $html .= '<li><a href="' . $baseUrl . '?page=' . $i . '" class="page-link' . $active . '">' . $i . '</a></li>';
    }

    if ($end < $totalPages) {
        if ($end < $totalPages - 1) $html .= '<li><span class="page-dots">...</span></li>';
        $html .= '<li><a href="' . $baseUrl . '?page=' . $totalPages . '" class="page-link">' . $totalPages . '</a></li>';
    }

    // Nút Next
    if ($currentPage < $totalPages) {
        $html .= '<li><a href="' . $baseUrl . '?page=' . ($currentPage + 1) . '" class="page-link">Tiếp &raquo;</a></li>';
    }

    $html .= '</ul></nav>';
    return $html;
}

/**
 * Rút gọn nội dung
 */
function excerpt(string $text, int $length = 150): string
{
    $text = strip_tags($text);
    if (mb_strlen($text) <= $length) return $text;
    return mb_substr($text, 0, $length) . '...';
}

/**
 * Định dạng ngày tiếng Việt
 */
function formatDate(string $date): string
{
    $timestamp = strtotime($date);
    return date('d/m/Y', $timestamp);
}

/**
 * Định dạng ngày giờ tiếng Việt
 */
function formatDateTime(string $date): string
{
    $timestamp = strtotime($date);
    return date('H:i - d/m/Y', $timestamp);
}

/**
 * Kiểm tra admin đã đăng nhập chưa
 */
function isLoggedIn(): bool
{
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

/**
 * Yêu cầu đăng nhập (redirect nếu chưa login)
 */
function requireLogin(): void
{
    if (!isLoggedIn()) {
        header('Location: ' . ADMIN_URL . '/login.php');
        exit;
    }
}

/**
 * Lấy trang hiện tại từ query string
 */
function getCurrentPage(): int
{
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    return max(1, $page);
}

/**
 * Tạo thông báo flash
 */
function setFlash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

/**
 * Hiển thị thông báo flash
 */
function getFlash(): string
{
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        $class = $flash['type'] === 'success' ? 'alert-success' : 'alert-error';
        return '<div class="alert ' . $class . '">' . sanitize($flash['message']) . '</div>';
    }
    return '';
}

/**
 * Đếm tổng bản ghi trong bảng
 */
function countRecords(PDO $pdo, string $table): int
{
    $allowed = [
        'places',
        'news',
        'contacts',
        'admins',
        'place_images',
        'foods',
        'homestays',
        'homestay_bookings',
        'users',
        'reviews'
    ];
    if (!in_array($table, $allowed)) return 0;
    $stmt = $pdo->query("SELECT COUNT(*) FROM `$table`");
    return (int)$stmt->fetchColumn();
}

/**
 * Tăng lượt xem
 */
function incrementViews(PDO $pdo, string $table, int $id): void
{
    $allowed = ['places', 'news', 'foods', 'homestays'];
    if (!in_array($table, $allowed)) return;
    $stmt = $pdo->prepare("UPDATE `$table` SET views = views + 1 WHERE id = ?");
    $stmt->execute([$id]);
}

/**
 * Kiểm tra người dùng đã đăng nhập chưa
 */
function isUserLoggedIn(): bool
{
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Bắt buộc user đăng nhập để truy cập chức năng
 */
function requireUserLogin(string $redirectPath = ''): void
{
    if (!isUserLoggedIn()) {
        $target = !empty($redirectPath) ? $redirectPath : ($_SERVER['REQUEST_URI'] ?? '/');
        header('Location: ' . SITE_URL . '/login.php?redirect=' . urlencode($target));
        exit;
    }
}

/**
 * Lấy thông tin user hiện tại
 */
function getCurrentUser(PDO $pdo): ?array
{
    if (!isUserLoggedIn()) {
        return null;
    }

    $stmt = $pdo->prepare("SELECT id, full_name, email FROM users WHERE id = ? AND is_active = 1");
    $stmt->execute([(int)$_SESSION['user_id']]);
    $user = $stmt->fetch();

    if (!$user) {
        unset($_SESSION['user_id'], $_SESSION['user_name']);
        return null;
    }

    $_SESSION['user_name'] = $user['full_name'];
    return $user;
}

/**
 * Định dạng giá tiền VND
 */
function formatPrice(float $price): string
{
    return number_format($price, 0, ',', '.') . ' đ';
}
