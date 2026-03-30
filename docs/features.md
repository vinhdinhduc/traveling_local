# CÁC CHỨC NĂNG CỦA HỆ THỐNG (FEATURES)

Hệ thống được chia thành 2 phân hệ chính: Phân hệ dành cho Người dùng (Frontend) và Phân hệ dành cho Quản trị viên (Admin Panel).

## 1. Phân hệ Người dùng (Frontend)

### 1.1. Đăng ký & Đăng nhập
*   **Tên chức năng:** Ghi danh và xác thực người dùng.
*   **Mô tả:** Cho phép khách hàng tạo tài khoản mới và đăng nhập để sử dụng các tính năng nâng cao (đặt phòng, đánh giá, yêu thích).
*   **Luồng xử lý:** 
    *   Nhập thông tin (Email, Mật khẩu, Họ Tên). 
    *   Validate định dạng email, kiểm tra độ an toàn mật khẩu.
    *   Mã hóa mật khẩu bằng `password_hash()` và lưu vào CSDL. 
    *   Khi đăng nhập, kiểm tra `password_verify()` và khởi tạo `$_SESSION['user_id']`.

### 1.2. Xem thông tin (Địa điểm, Tin tức, Ẩm thực)
*   **Tên chức năng:** Hiển thị danh mục và chi tiết.
*   **Mô tả:** Xem danh sách phân trang và chi tiết các địa danh du lịch, tin tức văn hóa, các món ăn đặc sản tại Vân Hồ.
*   **Luồng xử lý:** Query dữ liệu từ các bảng `places`, `news`, `foods` kèm theo truy vấn bảng `place_images` để sinh ra gallery.

### 1.3. Đặt phòng Homestay (Booking)
*   **Tên chức năng:** Đặt phòng và giữ chỗ homestay.
*   **Mô tả:** Cho phép người dùng chọn ngày lưu trú ở một homestay cụ thể và tiến hành đặt phòng.
*   **Luồng xử lý:** 
    *   Nhập form chọn Check-in, Check-out, số lượng khách.
    *   Hệ thống kiểm tra tính hợp lệ của ngày, tính toán tổng số tiền dựa trên `price_per_night`.
    *   Tạo bản ghi trong bảng `homestay_bookings` với trạng thái `pending` và set cờ thời gian giữ chỗ `hold_until` (10 phút).

### 1.4. Thanh toán quét mã QR (QR Payment Proof)
*   **Tên chức năng:** Xác nhận thanh toán đặt phòng qua biên lai.
*   **Mô tả:** Cung cấp mã VietQR tự động sinh theo thông tin ngân hàng của Admin và số tiền cần thanh toán. Cho phép upload ảnh bill điện tử.
*   **Luồng xử lý:** 
    *   Màn hình đếm ngược thời gian giữ chỗ. Sinh QR bằng API VietQR.
    *   Người dùng upload ảnh. Server validate định dạng file ảnh tĩnh (jpg, png...), lưu vào thư mục `uploads/payment-proofs`.
    *   Kích hoạt record mới trong bảng `payments` và dừng bộ đếm thời gian.

### 1.5. Đánh giá và Yêu thích (Review & Wishlist)
*   **Tên chức năng:** Tính năng tương tác với nội dung.
*   **Mô tả:** Người dùng đã đăng nhập có thể đánh giá điểm (1-5 sao) hoặc đánh dấu lưu giữ các địa điểm.
*   **Luồng xử lý:** Form POST gửi đánh giá. Lưu vào CSDL bảng `reviews` (chờ admin duyệt) hoặc lưu vào `wishlists`.

---

## 2. Phân hệ Quản trị (Admin Panel)

### 2.1. Đăng nhập Quản trị
*   **Tên chức năng:** Cổng đăng nhập nội bộ.
*   **Mô tả:** Dành riêng cho tài khoản có Role = `admin` để truy cập trang quản lý.
*   **Luồng xử lý:** Tương tự đăng nhập người dùng nhưng có kiểm tra trường `role == 'admin'` và điều hướng về `/admin/index.php`.

### 2.2. Quản lý Đặt phòng (Booking Management)
*   **Tên chức năng:** Xử lý đơn đặt phòng và duyệt thanh toán.
*   **Mô tả:** Admin xem danh sách đặt phòng, xác nhận hóa đơn chuyển khoản từ khách hàng.
*   **Luồng xử lý:** Đọc dữ liệu từ `homestay_bookings` kết hợp bảng `payments`. Admin đối chiếu ảnh chụp hoá đơn và chọn hành động `Accept` (xác nhận) hoặc `Reject` (từ chối/hủy).

### 2.3. Quản lý Địa điểm, Homestay, Ẩm thực, Tin tức (CRUD)
*   **Tên chức năng:** Trình quản lý nội dung đa tầng.
*   **Mô tả:** Cho phép thực hiện các thao tác Thêm/Đọc/Sửa/Xóa dữ liệu thông tin du lịch. Quản lý hình ảnh đại diện và slide ảnh (Gallery) của từng địa điểm.
*   **Luồng xử lý:** 
    *   Hiển thị list dạng bảng phân trang.
    *   Form thêm/sửa có tích hợp Editor và Upload files.
    *   Xóa bản ghi (có xác nhận) đi kèm xóa file ảnh thực tế trên ổ cứng nhằm giải phóng dung lượng.

### 2.4. Quản lý Phản hồi & Đánh giá (Contact & Review)
*   **Tên chức năng:** Moderation (Kiểm duyệt tương tác).
*   **Mô tả:** Kiểm duyệt các lời chỉ trích, đánh giá từ khách hàng.
*   **Luồng xử lý:** Mặc định `is_approved = 1` hoặc `0` tùy cấu hình. Admin duyệt hoặc xoá đánh giá vi phạm chuẩn mực. Đọc hòm thư Contact từ form ngoài Frontend.
