# CÁC ĐOẠN MÃ QUAN TRỌNG (IMPORTANT CODE)

Dưới đây là một số trích đoạn mã cốt lõi của hệ thống thể hiện các tư duy an toàn bảo mật, validate cũng như business flow của đồ án website.

## 1. Cơ chế Tạo và Xác minh CSRF Token (Security)
Chống lại các rủi ro kịch bản Cross-Site Request Forgery bằng việc mã hóa Token Session để đảm bảo Form đi liền trên trình duyệt.

**File:** `includes/config.php`
```php
// Hàm tạo CSRF Token độc nhất sử dụng cơ chế hash 32 bytes ngẫu nhiên
function generateCsrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Hàm xác thực CSRF Token (Ngăn ngừa Type Juggling comparison bằng hash_equals)
function verifyCsrfToken(string $token): bool
{
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
```
**Giải thích:**
Hàm sẽ tự động lưu `csrf_token` vào Session. Tại mỗi thẻ `<form>` trên Frontend sẽ nhúng chuỗi token đó. Khi `POST`, server luôn đối chiếu token gửi lên với Session thông qua hàm `hash_equals` an toàn (timing attack safe). Nếu khác sẽ văng lỗi phiên truy cập không thực.

---

## 2. Authentication Login Check
Toàn vẹn xác thực tài khoản và phân quyền người dùng. 

**File:** `login.php`
```php
$stmt = $pdo->prepare('SELECT id, full_name, email, password, is_active, role FROM users WHERE email = ? LIMIT 1');
$stmt->execute([$email]);
$user = $stmt->fetch();

// Xác thực pass theo thuật toán BCrypt được cấu thành từ hàm password_hash() trước đó
if ($user && (int)$user['is_active'] === 1 && password_verify($password, $user['password'])) {
    // Khởi tạo thông tin cá nhân chung
    $_SESSION['user_id'] = (int)$user['id'];
    $_SESSION['user_name'] = $user['full_name'];

    // Cấp cờ thẩm quyền riêng nếu Role thuộc cấp Admin
    if (($user['role'] ?? 'user') === 'admin') {
        $_SESSION['admin_id'] = (int)$user['id'];
        $_SESSION['admin_username'] = $user['full_name'];
    }

    // Refresh Session ID xoá định danh cũ ngăn Hacker tái sử dụng phiên
    session_regenerate_id(true);

    // Chuyển cửa sổ (Redirect URL Location)
    if (($user['role'] ?? 'user') === 'admin') {
        header('Location: ' . ADMIN_URL . '/index.php');
        exit;
    }
    
    header('Location: ' . SITE_URL);
    exit;
}
```
**Giải thích:**
*   Sử dụng Prepare Statement truy vấn trích lọc Email chống SQL Injection căn bản.
*   Chỉ xác minh đúng nếu trường `is_active` bằng 1 để admin cấm cản user phá rối khi cần.
*   Phương pháp `session_regenerate_id(true)` (Session Fixation Prevention) giải quyết việc đánh cắp con mồi trỏ Session Token cũ.

---

## 3. Quá trình Xác nhận Thanh toán và Giải phóng Đồng hồ 10 Phút
Xử lý giao dịch Booking (Mô-đun Upload Receipt).

**File:** `process_payment.php`
```php
try {
    // Kích hoạt mô hình giao dịch Transation tự rollback nếu hệ thống lỗi.
    $pdo->beginTransaction();

    // Lưu vào bảng payments thông tin đường dẫn Transaction
    $stmtInsert = $pdo->prepare('INSERT INTO payments (booking_id, amount, payment_method, status, transaction_code) VALUES (?, ?, "BANK_QR", "pending", ?)');
    $stmtInsert->execute([$bookingId, $amount, $transactionCode]);

    // RẤT QUAN TRỌNG: Đã có minh chứng chuyển khoản nên xoá đồng hồ giữ chỗ hẹn giờ 10 Phút (Set hold_until = NULL)
    // Để giữ booking không tự động Reset Cancel cho đến lúc chờ admin duyệt thật giả bill.
    $stmtBooking = $pdo->prepare('UPDATE homestay_bookings SET hold_until = NULL, updated_at = NOW() WHERE id = ? AND status = "pending"');
    $stmtBooking->execute([$bookingId]);

    // Đồng bộ hoàn tất
    $pdo->commit();

    setFlash('success', 'Đã gửi minh chứng chuyển khoản. Admin sẽ xác nhận booking trong thời gian sớm nhất.');
    header('Location: ' . SITE_URL . '/process_payment.php?booking_id=' . $bookingId);
    exit;
} catch (Throwable $e) {
    // Nếu có lỗi, hoàn phiên giao tác CSDL
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('[BANK_QR] Submit transfer proof failed: ' . $e->getMessage());
    $errors[] = 'Không thể lưu minh chứng thanh toán. Vui lòng thử lại.';
}
```
**Giải thích:**
Sử dụng MySQL `Transactions (beginTransaction, commit, rollback)` để hoàn toàn chắc chắn dữ liệu bảng hóa đơn thanh toán `payments` và việc xoá đếm ngược huỷ đơn trong `homestay_bookings` được toàn vẹn (ACID). Ràng buộc logic xoá cột `hold_until` giúp lưu trữ đơn vĩnh viễn trong thời gian chênh lệch chuyển lệnh nội bộ để phía Admin phê duyệt. Trang thái lỗi Server cũng sẽ được thu hồi RollBack sạch sẽ.
