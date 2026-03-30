# LUỒNG XỬ LÝ (SEQUENCE FLOWC)

Tài liệu này mô tả từng bước của các luồng nghiệp vụ quan trọng trong hệ thống.

---

## 1. Luồng Xác thực: Đăng nhập (Login Flow)

**Hoạt động:** Người dùng hoặc Quản trị viên nhập thông tin để đăng nhập.
**Các bước (Step-by-step):**
1.  **Client:** Trình duyệt tải trang đăng nhập (`login.php`). Hiển thị form gồm Email, Mật khẩu và một mã bảo mật ẩn (CSRF Token).
2.  **Client:** Người dùng điền thông tin và bấm Submit (gửi phương thức `POST`).
3.  **Server `login.php`:** Tiếp nhận request.
    *   Kiểm tra `csrf_token` khớp với `$_SESSION['csrf_token']` hay không. Ngăn chặn lỗi giả mạo.
    *   Validate dữ liệu: Kiểm tra chuỗi Email và Mật khẩu có rỗng hay không.
4.  **Server `Database`:** 
    *   Thực hiện truy vấn PDO lấy ra ID, tên, cờ trạng thái (`is_active`) và chuỗi mật khẩu bị băm (`password`) khớp với Email.
5.  **Server `login.php`:** 
    *   Nếu không có user khớp / `is_active` = 0 -> Thông báo lỗi.
    *   Nếu có, dùng hàm `password_verify(mật_khẩu_gốc, mật_khẩu_db)`:
        *   Sai -> Thông báo "Mật khẩu không khớp".
        *   Đúng -> Khởi tạo biến `$_SESSION['user_id']`.
6.  **Server `Session`:**
    *   Hệ thống kiểm tra trường quyền `role`. Nếu `role == 'admin'`, lưu thêm `$_SESSION['admin_id']`.
    *   Chạy hàm `session_regenerate_id(true)` để bảo vệ chống Fixation session.
7.  **Server:** 
    *   Trỏ Location (chuyển hướng web) về trang chủ `/index.php` (với Use) hoặc màn `/admin/index.php` (với Admin).
8.  **Client:** Trình duyệt load trang tương ứng với trạng thái đã đăng nhập.

---

## 2. Luồng Xử lý Dữ liệu: Thêm một Tin tức (CRUD Data Flow)

**Hoạt động:** Admin đăng một bài viết tin tức mới vào CSDL.
**Các bước (Step-by-step):**
1.  **Admin:** Bấm vào mục thêm tin tức trên Admin Panel. Tải trang `admin/news/add.php`.
2.  **Admin:** Điền thông tin tiêu đề, mô tả (rich-text editor), và chọn tải lên tệp ảnh minh hoạ. Gửi `POST / Multipart-form-data`.
3.  **Server `admin/news/add.php`:**
    *   Kiểm tra phiên đăng nhập Admin và chứng thực CSRF.
4.  **Server `File System`:** Xử lý ảnh tải lên.
    *   Kiểm tra kích thước tệp tải lên (<= 5MB).
    *   Kiểm tra đuôi mở rộng hợp lệ (`jpg`, `png`, `webp`).
    *   Đổi tên file độc nhất ngẫu nhiên (chống ghi đè nội dung) và copy file vào `/uploads/news/` (hàm `move_uploaded_file()`).
5.  **Server `Database`:** 
    *   Tự động phát sinh chuỗi `slug` tĩnh (từ "Tin Vui" thành `tin-vui`).
    *   Thực thi lệnh SQL `INSERT INTO news (title, slug, content, excerpt, image) VALUES (...)` theo Prepare Statement PDO an toàn.
6.  **Server:** Set Session Flash Message (thông báo popup thêm thành công). Chuyển hướng về trang danh sách `admin/news/index.php`.
7.  **Admin:** Nhận kết quả hoàn thành hiển thị trên bảng.

---

## 3. Luồng Nghiệp vụ: Đặt phòng và Thanh toán (Booking & Payment Flow)

**Hoạt động:** Khách hàng tiến hành đặt một phòng Homestay và quét mã thanh toán bằng hoá đơn ngân hàng.
**Các bước (Step-by-step):**

### Giai đoạn 1: Đặt đơn (Booking)
1.  **Client (Người dùng):** Xem chi tiết homestay. Chọn điền số ngày gửi (`check_in`, `check_out`), số phòng hoặc số người tham gia rồi Submit Form Booking.
2.  **Server `homestay-detail.php`:** 
    *   Bắt buộc User phải đã `Login`. (Nếu chưa thì Redirect về cổng đăng nhập).
    *   Ràng buộc logic: Ngày `check_in` không được về quá khứ, `check_out` lớn hơn `check_in`.
    *   Tính tổng chi phí hoá đơn (`total_price`).
    *   Chèn một đơn hàng vào DB (`homestay_bookings`) trạng thái = `'pending'`, `payment_status`=`'unpaid'`. Set `hold_until` đếm ngược hệ thống thời gian rảnh 10 phút trôi qua.
3.  **Server:** Chuyển hướng người thụ hưởng đến trang `/payment.php?booking_id=123`.

### Giai đoạn 2: Quét mã Thanh toán (Payment & Upload Proof)
4.  **Client (Người dùng):** Màn hình hiển thị mã VietQR (có chứa BIN Ngân hàng, STK, Số tiền và nội dung `BOOKING 123`). Máy đếm ngược thời gian giữ chỗ bằng JS.
5.  **Client:** Khách hàng lấy điện thoại quét QR App Banking, chuyển phát thành công. Sau đó chụp ảnh màn hình biên lai hoàn tất. Tải cái ảnh đó cho hệ thống trên trang web.
6.  **Server `process_payment.php`:** 
    *   Tiếp nhận file ảnh biên lai từ khách. Kiểm tra đúng ảnh (PNG/JPG) không quá cỡ.
    *   Lưu vào `uploads/payment-proofs`.
    *   Insert vào bảng phụ `payments` trạng thái `'pending'`.
    *   Cập nhật huỷ thời gian đếm ngược của bảng `homestay_bookings` (`hold_until = NULL`) => Khóa giữ chỗ để đợi Admin duyệt. Tránh bị huỷ nhầm đơn sau 10 phút.
7.  **Client:** Màn hình hệ thống ghi báo "Đã nhận minh chứng thành công. Đợi Admin duyệt".

### Giai đoạn 3: Phê duyệt (Admin Verify)
8.  **Admin:** Nhận thông báo. Mở `/admin/payments/index.php`.
9.  **Admin:** Click xem ảnh biên lai, check tiền đổ về STK hệ thống. Sau đó bấm Cập nhật thanh toán. 
10. **Server:** Cập nhật DB `payments.status`=`'success'` và `homestay_bookings.payment_status`=`'paid'`, `status`=`'confirmed'`. 
    *   *(Kết thúc chuỗi quy trình)*
