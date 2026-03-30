# THIẾT KẾ CƠ SỞ DỮ LIỆU (DATABASE DESIGN)

Hệ quản trị CSDL: **MySQL** (hoặc MariaDB).
Tên Database: `vanho_tourism`
Collation: `utf8mb4_unicode_ci`

Dưới đây là cấu trúc các bảng chính trong hệ thống và phân tích chức năng:

## 1. Danh sách các bảng (Tables)

### 1.1. Bảng `users` (Người dùng)
Lưu trữ thông tin tài khoản đăng nhập của hệ thống.
*   `id` (INT, PK, AI): Mã người dùng.
*   `full_name` (VARCHAR): Họ và tên.
*   `email` (VARCHAR, UNIQUE): Địa chỉ email đăng nhập.
*   `password` (VARCHAR): Mật khẩu đã mã hoá Hash (Bcrypt).
*   `phone` (VARCHAR): Số điện thoại.
*   `role` (ENUM): Quyền hạn (`'user'` hoặc `'admin'`). Giá trị mặc định là `'user'`.
*   `is_active` (TINYINT): Trạng thái khoá tài khoản (1: Hoạt động, 0: Bị khoá).

### 1.2. Bảng `places` (Địa điểm du lịch)
*   `id` (INT, PK, AI): Mã địa điểm.
*   `name` (VARCHAR): Tên địa điểm.
*   `slug` (VARCHAR, UNIQUE): Đường dẫn SEO tĩnh.
*   `short_description` (TEXT): Đoạn mô tả ngắn.
*   `description` (LONGTEXT): Nội dung giới thiệu chi tiết (HTML).
*   `location` (VARCHAR): Vị trí hành chính.
*   `map_embed` (TEXT): Mã iframe Google Maps.
*   `image` (VARCHAR): Tên file ảnh đại diện.
*   `views` (INT): Số lượt xem trang.

*(Bảng phụ: `place_images` chứa danh sách ảnh thư viện Gallery (foreign key `place_id`)).*

### 1.3. Bảng `homestays` (Lưu trú Homestay)
*   `id` (INT, PK, AI): Mã Homestay.
*   `name` (VARCHAR): Tên Homestay.
*   `slug` (VARCHAR): Đường dẫn SEO.
*   `price_per_night` (DECIMAL): Giá phòng trung bình mỗi đêm.
*   `total_rooms` (INT): Tổng số phòng khách có thể tuỳ chọn.
*   `address`, `description`, `image`, `views`: Tương tự như bảng `places`.

### 1.4. Bảng `homestay_bookings` (Đặt phòng Homestay)
Lưu trữ các giao dịch đặt giữ chỗ.
*   `id` (INT, PK, AI): Mã Booking.
*   `homestay_id` (INT, FK): Trỏ đến mã homestay lưu trú.
*   `user_id` (INT, FK): Trỏ đến khách hàng đặt phòng.
*   `check_in` (DATE): Ngày nhận phòng.
*   `check_out` (DATE): Ngày trả phòng.
*   `guests` (INT): Số lượng khách đi cùng.
*   `total_price` (DECIMAL): Tổng tiền phải thanh toán (`price_per_night * số đêm`).
*   `status` (ENUM): Trạng thái xử lý (`pending`, `confirmed`, `cancelled`).
*   `hold_until` (DATETIME): Thời hạn giữ chỗ (ví dụ: +10 phút kể từ lúc đặt) cho đến khi nhận được xác nhận thanh toán.
*   `payment_status` (ENUM): Tình trạng thanh toán (`unpaid`, `paid`, `refunded`).

### 1.5. Bảng `payments` (Thanh toán Giao dịch)
*   `id` (INT, PK, AI): Mã giao dịch thanh toán.
*   `booking_id` (INT, FK): Ràng buộc đơn đặt phòng.
*   `amount` (DECIMAL): Số tiền giao dịch.
*   `payment_method` (VARCHAR): Phương thức (VD: `BANK_QR`).
*   `status` (ENUM): Trạng thái (`pending`, `success`, `failed`).
*   `transaction_code` (VARCHAR): Mã hoá tham chiếu kết hợp hình ảnh upload làm minh chứng chuyển khoản.

### 1.6. Các bảng chức năng tĩnh (Tin tức, Ẩm thực, Liên hệ)
*   **`news`**: Bài viết tin tức (`title`, `slug`, `content`, `excerpt`, `image`).
*   **`foods`**: Món ăn đặc sản (`name`, `description`, `image`, `views`).
*   **`contacts`**: Thư viện liên hệ (`name`, `email`, `message`, `is_read`).

### 1.7. Các bảng Tương tác (Đánh giá, Yêu thích)
*   **`reviews`**: Nhận xét về Địa điểm (trỏ khoá ngoại `place_id`, `user_id`, tính điểm `rating` 1-5).
*   **`homestay_reviews`**: Nhận xét Homestay (tương tự `reviews`).
*   **`wishlists`**: Đánh dấu địa điểm yêu thích (trỏ `user_id` và `place_id`).

---

## 2. Các Mối quan hệ ràng buộc (Relationships)

Cơ sở dữ liệu thiếp lập cấu trúc ràng buộc toàn phần theo Referential Integrity - Xóa theo dạng chuỗi (CASCADE DELETE) để giữ cho Database gọn gàng và tránh rác (Orphaned records).

1.  **Một Homestay có nhiều Đặt phòng (1 - N)**
    *   `homestay_bookings.homestay_id` --> `homestays.id` (ON DELETE CASCADE)
2.  **Một Người dùng có nhiều Đặt phòng (1 - N)**
    *   `homestay_bookings.user_id` --> `users.id` (ON DELETE CASCADE)
3.  **Một Đặt phòng có nhiều Lần Thanh toán (1 - N)**
    *   `payments.booking_id` --> `homestay_bookings.id` (ON DELETE CASCADE)
4.  **Một Địa điểm có nhiều Ảnh Gallery (1 - N)**
    *   `place_images.place_id` --> `places.id` (ON DELETE CASCADE)
5.  **Một Người dùng tương tác (Đánh giá, Yêu thích) nhiều Địa điểm/Homestay (N - N Mapping Tables)**
    *   `reviews` cầu nối giữa `users` và `places`.
    *   `wishlists` cầu nối giữa `users` và `places`.
    *   `homestay_reviews` cầu nối giữa `users` và `homestays`.
