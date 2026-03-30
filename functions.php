<?php

function sanitize(string $data): string
{
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

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

function uploadImage(array $file, string $folder = 'places'): string|false
{
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    if ($file['size'] > MAX_FILE_SIZE) {
        return false;
    }

    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, ALLOWED_EXTENSIONS, true)) {
        return false;
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);
    $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/jfif'];
    if (!in_array($mimeType, $allowedMimes, true)) {
        return false;
    }

    $newName = time() . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
    $uploadDir = UPLOADS_PATH . $folder . '/';

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $uploadPath = $uploadDir . $newName;
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

function getImageUrl(string $filename, string $folder = 'places'): string
{
    if (empty($filename)) {
        return SITE_URL . '/assets/images/no-image.jpg';
    }
    return UPLOADS_URL . '/' . $folder . '/' . $filename;
}

function pagination(int $totalItems, int $perPage, int $currentPage, string $baseUrl): string
{
    $totalPages = (int)ceil($totalItems / $perPage);
    if ($totalPages <= 1) {
        return '';
    }

    $separator = str_contains($baseUrl, '?') ? '&' : '?';
    $html = '<nav class="pagination-wrapper"><ul class="pagination">';

    if ($currentPage > 1) {
        $html .= '<li><a href="' . $baseUrl . $separator . 'page=' . ($currentPage - 1) . '" class="page-link">&laquo; Trước</a></li>';
    }

    $start = max(1, $currentPage - 2);
    $end = min($totalPages, $currentPage + 2);

    if ($start > 1) {
        $html .= '<li><a href="' . $baseUrl . $separator . 'page=1" class="page-link">1</a></li>';
        if ($start > 2) {
            $html .= '<li><span class="page-dots">...</span></li>';
        }
    }

    for ($i = $start; $i <= $end; $i++) {
        $active = ($i === $currentPage) ? ' active' : '';
        $html .= '<li><a href="' . $baseUrl . $separator . 'page=' . $i . '" class="page-link' . $active . '">' . $i . '</a></li>';
    }

    if ($end < $totalPages) {
        if ($end < $totalPages - 1) {
            $html .= '<li><span class="page-dots">...</span></li>';
        }
        $html .= '<li><a href="' . $baseUrl . $separator . 'page=' . $totalPages . '" class="page-link">' . $totalPages . '</a></li>';
    }

    if ($currentPage < $totalPages) {
        $html .= '<li><a href="' . $baseUrl . $separator . 'page=' . ($currentPage + 1) . '" class="page-link">Tiếp &raquo;</a></li>';
    }

    $html .= '</ul></nav>';
    return $html;
}

function excerpt(string $text, int $length = 150): string
{
    $text = strip_tags($text);
    if (mb_strlen($text) <= $length) {
        return $text;
    }
    return mb_substr($text, 0, $length) . '...';
}

function formatDate(string $date): string
{
    return date('d/m/Y', strtotime($date));
}

function formatDateTime(string $date): string
{
    return date('H:i - d/m/Y', strtotime($date));
}

function isLoggedIn(): bool
{
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

function requireLogin(): void
{
    if (!isLoggedIn()) {
        $target = $_SERVER['REQUEST_URI'] ?? (ADMIN_URL . '/index.php');
        $redirect = str_starts_with($target, 'http') ? $target : (SITE_URL . $target);
        header('Location: ' . SITE_URL . '/login.php?redirect=' . urlencode($redirect));
        exit;
    }
}

function isUserLoggedIn(): bool
{
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function requireUserLogin(string $redirectPath = ''): void
{
    if (!isUserLoggedIn()) {
        $target = !empty($redirectPath) ? $redirectPath : ($_SERVER['REQUEST_URI'] ?? '/');
        header('Location: ' . SITE_URL . '/login.php?redirect=' . urlencode($target));
        exit;
    }
}

function getCurrentUser(PDO $pdo): ?array
{
    if (!isUserLoggedIn()) {
        return null;
    }

    $stmt = $pdo->prepare('SELECT id, full_name, email, role FROM users WHERE id = ? AND is_active = 1');
    $stmt->execute([(int)$_SESSION['user_id']]);
    $user = $stmt->fetch();

    if (!$user) {
        unset($_SESSION['user_id'], $_SESSION['user_name']);
        return null;
    }

    $_SESSION['user_name'] = $user['full_name'];
    return $user;
}

function getCurrentPage(): int
{
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    return max(1, $page);
}

function setFlash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash(): string
{
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        $class = ($flash['type'] ?? 'error') === 'success' ? 'alert-success' : 'alert-error';
        return '<div class="alert ' . $class . '">' . sanitize((string)$flash['message']) . '</div>';
    }
    return '';
}

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
        'reviews',
        'homestay_reviews'
    ];
    if (!in_array($table, $allowed, true)) {
        return 0;
    }
    $stmt = $pdo->query("SELECT COUNT(*) FROM `$table`");
    return (int)$stmt->fetchColumn();
}

function incrementViews(PDO $pdo, string $table, int $id): void
{
    $allowed = ['places', 'news', 'foods', 'homestays'];
    if (!in_array($table, $allowed, true)) {
        return;
    }
    $stmt = $pdo->prepare("UPDATE `$table` SET views = views + 1 WHERE id = ?");
    $stmt->execute([$id]);
}

function formatPrice(float $price): string
{
    return number_format($price, 0, ',', '.') . ' đ';
}

function buildQueryString(array $override = []): string
{
    $query = $_GET;
    foreach ($override as $key => $value) {
        if ($value === null || $value === '') {
            unset($query[$key]);
        } else {
            $query[$key] = $value;
        }
    }
    return http_build_query($query);
}

function releaseExpiredPendingBookings(PDO $pdo): void
{
    $sql = 'UPDATE homestay_bookings b
            SET b.status = "cancelled"
            WHERE b.status = "pending"
              AND b.hold_until IS NOT NULL
              AND b.hold_until < NOW()
              AND NOT EXISTS (
                    SELECT 1 FROM payments p
                    WHERE p.booking_id = b.id
                      AND p.payment_method = "BANK_QR"
                      AND p.status = "pending"
              )';
    $pdo->exec($sql);
}

function isHomestayAvailable(PDO $pdo, int $homestayId, string $checkIn, string $checkOut): bool
{
    releaseExpiredPendingBookings($pdo);

    $stmtRooms = $pdo->prepare('SELECT total_rooms FROM homestays WHERE id = ? LIMIT 1');
    $stmtRooms->execute([$homestayId]);
    $totalRooms = (int)($stmtRooms->fetchColumn() ?: 1);
    if ($totalRooms < 1) {
        $totalRooms = 1;
    }

    $sql = 'SELECT COUNT(*) FROM homestay_bookings
            WHERE homestay_id = :homestay_id
              AND status IN ("pending", "confirmed")
              AND (status <> "pending" OR hold_until IS NULL OR hold_until >= NOW())
              AND check_in < :check_out
              AND check_out > :check_in';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':homestay_id' => $homestayId,
        ':check_in' => $checkIn,
        ':check_out' => $checkOut,
    ]);
    $bookedRooms = (int)$stmt->fetchColumn();

    return $bookedRooms < $totalRooms;
}

function createPendingBooking(PDO $pdo, int $homestayId, int $userId, string $checkIn, string $checkOut, int $guests, float $totalPrice, string $note = ''): int
{
    $sql = 'INSERT INTO homestay_bookings
            (homestay_id, user_id, check_in, check_out, guests, total_price, note, status, hold_until, payment_status)
            VALUES
            (:homestay_id, :user_id, :check_in, :check_out, :guests, :total_price, :note, "pending", DATE_ADD(NOW(), INTERVAL 10 MINUTE), "unpaid")';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':homestay_id' => $homestayId,
        ':user_id' => $userId,
        ':check_in' => $checkIn,
        ':check_out' => $checkOut,
        ':guests' => $guests,
        ':total_price' => $totalPrice,
        ':note' => $note,
    ]);
    return (int)$pdo->lastInsertId();
}

function getBookingByIdForUser(PDO $pdo, int $bookingId, int $userId): ?array
{
    $sql = 'SELECT b.*, h.name AS homestay_name
            FROM homestay_bookings b
            JOIN homestays h ON h.id = b.homestay_id
            WHERE b.id = ? AND b.user_id = ?
            LIMIT 1';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$bookingId, $userId]);
    $booking = $stmt->fetch();
    return $booking ?: null;
}

function confirmBookingPayment(PDO $pdo, int $bookingId, string $method, float $amount, ?string $transactionCode = null): void
{
    $pdo->beginTransaction();
    try {
        $stmtPayment = $pdo->prepare('INSERT INTO payments (booking_id, amount, payment_method, status, transaction_code) VALUES (?, ?, ?, "success", ?)');
        $stmtPayment->execute([$bookingId, $amount, $method, $transactionCode]);

        $stmtBooking = $pdo->prepare('UPDATE homestay_bookings
            SET status = "confirmed", payment_status = "paid", hold_until = NULL, updated_at = NOW()
            WHERE id = ?');
        $stmtBooking->execute([$bookingId]);

        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }
}

function generateFakeTransactionCode(string $prefix = 'PAY'): string
{
    return $prefix . '-' . date('YmdHis') . '-' . strtoupper(bin2hex(random_bytes(3)));
}

function buildQrPaymentTransactionCode(string $reference, string $proofImage): string
{
    $ref = preg_replace('/[^A-Za-z0-9_-]/', '', $reference);
    $proof = preg_replace('/[^A-Za-z0-9._-]/', '', $proofImage);
    return 'QR|ref=' . $ref . '|proof=' . $proof;
}

function parseQrPaymentTransactionCode(?string $transactionCode): array
{
    $result = ['reference' => '', 'proof_image' => ''];

    if (empty($transactionCode) || !str_starts_with($transactionCode, 'QR|')) {
        return $result;
    }

    $parts = explode('|', $transactionCode);
    foreach ($parts as $part) {
        if (str_starts_with($part, 'ref=')) {
            $result['reference'] = substr($part, 4);
        }
        if (str_starts_with($part, 'proof=')) {
            $result['proof_image'] = substr($part, 6);
        }
    }

    return $result;
}

function buildVietQrImageUrl(string $bankBin, string $accountNo, float $amount, string $addInfo, string $accountName = ''): string
{
    $bankBin = preg_replace('/\D/', '', $bankBin);
    $accountNo = preg_replace('/\D/', '', $accountNo);
    $amountValue = max(0, (int)round($amount));

    $base = 'https://img.vietqr.io/image/' . $bankBin . '-' . $accountNo . '-compact2.png';
    $query = ['amount' => $amountValue, 'addInfo' => $addInfo];
    if ($accountName !== '') {
        $query['accountName'] = $accountName;
    }

    return $base . '?' . http_build_query($query, '', '&', PHP_QUERY_RFC3986);
}

function isPlaceWishlisted(PDO $pdo, int $userId, int $placeId): bool
{
    $stmt = $pdo->prepare('SELECT 1 FROM wishlists WHERE user_id = ? AND place_id = ? LIMIT 1');
    $stmt->execute([$userId, $placeId]);
    return (bool)$stmt->fetchColumn();
}

function toggleWishlist(PDO $pdo, int $userId, int $placeId): bool
{
    if (isPlaceWishlisted($pdo, $userId, $placeId)) {
        $stmt = $pdo->prepare('DELETE FROM wishlists WHERE user_id = ? AND place_id = ?');
        $stmt->execute([$userId, $placeId]);
        return false;
    }

    $stmt = $pdo->prepare('INSERT INTO wishlists (user_id, place_id) VALUES (?, ?)');
    $stmt->execute([$userId, $placeId]);
    return true;
}

function renderStars(int $rating): string
{
    $rating = max(0, min(5, $rating));
    $html = '';
    for ($i = 1; $i <= 5; $i++) {
        $html .= $i <= $rating
            ? '<i class="fas fa-star" style="color:#f59e0b"></i>'
            : '<i class="far fa-star" style="color:#cbd5e1"></i>';
    }
    return $html;
}

function getMailConfig(PDO $pdo): array
{
    if (!function_exists('getSettingsByGroup') || !function_exists('getSetting')) {
        require_once __DIR__ . '/includes/settings.php';
    }

    $emailSettings = getSettingsByGroup($pdo, 'email');

    $config = [
        'host' => trim((string)($emailSettings['smtp_host'] ?? '')),
        'port' => (int)($emailSettings['smtp_port'] ?? 587),
        'username' => trim((string)($emailSettings['smtp_username'] ?? '')),
        'password' => (string)($emailSettings['smtp_password'] ?? ''),
        'encryption' => trim((string)($emailSettings['smtp_encryption'] ?? 'tls')),
        'from_email' => trim((string)($emailSettings['smtp_from_email'] ?? '')),
        'from_name' => trim((string)($emailSettings['smtp_from_name'] ?? 'Du lich Van Ho')),
    ];

    if ($config['host'] === '' || $config['username'] === '' || $config['from_email'] === '') {
        $fallbackPath = __DIR__ . '/includes/email.php';
        if (file_exists($fallbackPath)) {
            $fallback = require $fallbackPath;
            if (is_array($fallback)) {
                $smtp = (array)($fallback['smtp'] ?? []);
                if ($config['host'] === '') {
                    $config['host'] = (string)($smtp['host'] ?? '');
                }
                if ($config['port'] <= 0) {
                    $config['port'] = (int)($smtp['port'] ?? 587);
                }
                if ($config['username'] === '') {
                    $config['username'] = (string)($smtp['username'] ?? '');
                }
                if ($config['password'] === '') {
                    $config['password'] = (string)($smtp['password'] ?? '');
                }
                if ($config['from_email'] === '') {
                    $config['from_email'] = (string)($fallback['from_email'] ?? '');
                }
                if ($config['from_name'] === '') {
                    $config['from_name'] = (string)($fallback['from_name'] ?? 'Du lich Van Ho');
                }
                if ($config['encryption'] === '') {
                    $config['encryption'] = (string)($smtp['encryption'] ?? 'tls');
                }
            }
        }
    }

    if ($config['port'] <= 0) {
        $config['port'] = 587;
    }

    $config['encryption'] = strtolower($config['encryption']);
    if (!in_array($config['encryption'], ['tls', 'ssl', 'starttls'], true)) {
        $config['encryption'] = 'tls';
    }

    if ($config['from_name'] === '') {
        $config['from_name'] = 'Du lich Van Ho';
    }

    return $config;
}

function isMailConfigured(PDO $pdo): bool
{
    $config = getMailConfig($pdo);
    return $config['host'] !== '' && $config['username'] !== '' && $config['from_email'] !== '';
}

function sendEmail(PDO $pdo, string $to, string $subject, string $htmlBody): bool
{
    $config = getMailConfig($pdo);

    if ($to === '' || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
        error_log('[MAILER] Invalid recipient email: ' . $to);
        return false;
    }

    if ($config['host'] === '' || $config['from_email'] === '') {
        error_log('[MAILER] SMTP is not configured. Skip sending to: ' . $to);
        return false;
    }

    $phpMailerMain = __DIR__ . '/PHPMailer/PHPMailer.php';
    $phpMailerSMTP = __DIR__ . '/PHPMailer/SMTP.php';
    $phpMailerEx = __DIR__ . '/PHPMailer/Exception.php';

    if (file_exists($phpMailerMain) && file_exists($phpMailerSMTP) && file_exists($phpMailerEx)) {
        require_once $phpMailerMain;
        require_once $phpMailerSMTP;
        require_once $phpMailerEx;
        return sendWithPHPMailer($config, $to, $subject, $htmlBody);
    }

    return sendWithNativeMail($config, $to, $subject, $htmlBody);
}

function sendWithPHPMailer(array $config, string $to, string $subject, string $htmlBody): bool
{
    try {
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);

        $mail->isSMTP();
        $mail->Host = $config['host'];
        $mail->SMTPAuth = true;
        $mail->Username = $config['username'];
        $mail->Password = $config['password'];
        $mail->Port = (int)$config['port'];
        $mail->CharSet = 'UTF-8';
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true,
            ],
        ];

        if ($config['encryption'] === 'ssl') {
            $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
        } else {
            $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        }

        $mail->setFrom($config['from_email'], $config['from_name']);
        $mail->addAddress($to);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $htmlBody;
        $mail->AltBody = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $htmlBody));

        $mail->send();
        return true;
    } catch (Throwable $e) {
        error_log('[MAILER] PHPMailer error: ' . $e->getMessage());
        return false;
    }
}

function sendWithNativeMail(array $config, string $to, string $subject, string $htmlBody): bool
{
    $headers = [
        'MIME-Version: 1.0',
        'Content-Type: text/html; charset=UTF-8',
        'From: ' . $config['from_name'] . ' <' . $config['from_email'] . '>',
        'Reply-To: ' . $config['from_email'],
        'X-Mailer: VanhoTourism/1.0',
    ];

    $result = @mail($to, $subject, $htmlBody, implode("\r\n", $headers));
    if (!$result) {
        error_log('[MAILER] native mail() failed to: ' . $to . ' subject: ' . $subject);
    }

    return $result;
}

function getEmailTemplate(PDO $pdo, string $key): ?array
{
    $stmt = $pdo->prepare('SELECT * FROM email_templates WHERE template_key = ? LIMIT 1');
    $stmt->execute([$key]);
    $template = $stmt->fetch();
    return $template ?: null;
}

function sendTemplateEmail(PDO $pdo, string $templateKey, string $to, array $vars = []): bool
{
    $template = getEmailTemplate($pdo, $templateKey);
    if (!$template) {
        error_log('[MAILER] Template not found: ' . $templateKey);
        return false;
    }

    if (!function_exists('getSetting')) {
        require_once __DIR__ . '/includes/settings.php';
    }

    $vars['site_name'] = $vars['site_name'] ?? getSetting($pdo, 'site_name', SITE_NAME);
    $vars['site_url'] = $vars['site_url'] ?? SITE_URL;
    $vars['site_address'] = $vars['site_address'] ?? getSetting($pdo, 'site_address', '');
    $vars['site_email'] = $vars['site_email'] ?? getSetting($pdo, 'site_email', '');

    if (isset($vars['full_name']) && !isset($vars['customer_name'])) {
        $vars['customer_name'] = $vars['full_name'];
    }
    if (isset($vars['site_url']) && !isset($vars['home_url'])) {
        $vars['home_url'] = $vars['site_url'];
    }

    $subject = (string)$template['subject'];
    $body = (string)$template['body_html'];

    foreach ($vars as $key => $value) {
        $placeholder = '{{' . $key . '}}';
        $subject = str_replace($placeholder, (string)$value, $subject);
        $body = str_replace($placeholder, (string)$value, $body);
    }

    return sendEmail($pdo, $to, $subject, $body);
}
