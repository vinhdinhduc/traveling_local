# TỔNG QUAN HỆ THỐNG (SYSTEM OVERVIEW)

## 1. Giới thiệu chung
Website "Du lịch Vân Hồ" là một hệ thống cung cấp thông tin du lịch, quảng bá các địa danh, văn hóa, ẩm thực tại khu vực xã Vân Hồ, huyện Vân Hồ, tỉnh Sơn La. Hệ thống cho phép du khách tìm kiếm thông tin địa điểm, tin tức, đặt phòng homestay, xem bản đồ và để lại các đánh giá.

## 2. Kiến trúc hệ thống
Hệ thống được thiết kế theo mô hình Client - Server cơ bản với các thành phần:

*   **Frontend (Giao diện người dùng - Client):** Cung cấp các trang giao diện trực quan cho du khách (Trang chủ, Tin tức, Địa điểm, Ẩm thực, Homestay, Thanh toán...).
*   **Backend (Xử lý nghiệp vụ - Server):** Xử lý logic, xác thực người dùng, quản lý phiên làm việc (session), tương tác với CSDL. Bao gồm User API/Flow và Admin Panel để quản trị hệ thống.
*   **Database (Cơ sở dữ liệu):** Lưu trữ toàn bộ dữ liệu của hệ thống, bao gồm tin tức, địa điểm, người dùng, lượt đặt phòng, thanh toán, v.v.

## 3. Công nghệ sử dụng
Hệ thống sử dụng các công nghệ Web truyền thống (LAMP/WAMP stack):

### 3.1. Frontend
*   **HTML5 & CSS3:** Trình bày và định dạng nội dung giao diện.
*   **JavaScript (ES6):** Xử lý tương tác người dùng, DOM manipulation.
*   **Thư viện:** SwiperJS (tạo slider/carousel ảnh), FontAwesome (Icon), Google Maps Embed API (hiển thị bản đồ).
*   **Kiến trúc:** Responsive Design (Mobile-first).

### 3.2. Backend
*   **Ngôn ngữ lập trình:** PHP 8.x.
*   **Kiến trúc:** Cấu trúc tổ chức file hướng thủ tục kết hợp include modules (`includes/header.php`, `includes/footer.php`, `includes/config.php`).
*   **Bảo mật:**
    *   CSRF Token để chống tấn công Cross-Site Request Forgery.
    *   PDO Prepared Statements chống SQL Injection.
    *   `password_hash()` (bcrypt) đối với mật khẩu người dùng.
    *   XSS Protection qua hàm làm sạch chuỗi (`htmlspecialchars`).

### 3.3. Database
*   **Hệ quản trị CSDL:** MySQL (hoặc MariaDB).
*   **Kiểu kết nối:** PDO (PHP Data Objects).

## 4. Luồng hoạt động chính (Core Flow)

### 4.1. Luồng Người dùng (Guest & User)
1.  **Truy cập hệ thống:** Người dùng vào website, xem thông tin (Địa điểm, Tin tức, Ẩm thực) không cần đăng nhập.
2.  **Đăng ký/Đăng nhập:** Tạo tài khoản và xác thực qua session (email & password).
3.  **Đặt phòng Homestay:** 
    *   Người dùng đăng nhập chọn Homestay, nhập ngày Check-in/Check-out.
    *   Hệ thống tính tổng tiền, sinh mã đặt phòng (Booking).
4.  **Thanh toán Booking:** Người dùng quét mã VietQR và tải lên ảnh minh chứng thanh toán. Quá trình này được đặt lịch đếm ngược giữ chỗ (10 phút).
5.  **Tương tác khác:** Viết đánh giá cho địa điểm hoặc homestay, thêm địa điểm vào danh sách yêu thích (Wishlist), gửi form Contact.

### 4.2. Luồng Quản trị viên (Admin)
1.  **Đăng nhập Admin Panel:** Truy cập `/admin`, xác thực quyền (Role = `admin`).
2.  **Quản lý nội dung:** Thêm, xem, sửa, xóa (CRUD) danh sách Địa điểm, Homestay, Ẩm thực, Tin tức. Upload và duyệt hệ thống hình ảnh lưu trong `/uploads`.
3.  **Quản lý giao dịch:** Kiểm tra các ảnh minh chứng thanh toán của người dùng, xác nhận (Duyệt) trạng thái đặt phòng.
4.  **Quản lý tương tác:** Xem các tin nhắn liên hệ (Contact), duyệt/xóa đánh giá của người dùng.
