# 🏔️ Du lịch Vân Hồ - Website quảng bá du lịch

Website quảng bá du lịch xã Vân Hồ, huyện Vân Hồ, tỉnh Sơn La.  
Được xây dựng bằng PHP 8+, MySQL, HTML5, CSS3, JavaScript và SwiperJS.

---

## 📋 Yêu cầu hệ thống

- **PHP** 8.0 trở lên
- **MySQL** 5.7+ hoặc MariaDB 10.3+
- **Apache** với mod_rewrite (hoặc Nginx)
- **XAMPP / WAMP / LAMP** (khuyên dùng cho development)

---

## 🚀 Hướng dẫn cài đặt

### Bước 1: Clone / Copy source code

Copy toàn bộ thư mục `vanho-tourism` vào thư mục web server:

- **XAMPP**: `C:\xampp\htdocs\vanho-tourism\`
- **WAMP**: `C:\wamp64\www\vanho-tourism\`
- **Linux**: `/var/www/html/vanho-tourism/`

### Bước 2: Tạo Database

1. Mở **phpMyAdmin**: `http://localhost/phpmyadmin`
2. Tạo database mới tên: `vanho_tourism`
   - Character set: `utf8mb4`
   - Collation: `utf8mb4_unicode_ci`
3. Import file `database.sql`:
   - Chọn database `vanho_tourism`
   - Vào tab **Import**
   - Chọn file `database.sql` từ thư mục dự án
   - Nhấn **Go**

Hoặc dùng command line:

```bash
mysql -u root -p vanho_tourism < database.sql
```

### Bước 3: Cấu hình kết nối Database

Mở file `includes/config.php` và chỉnh sửa thông tin kết nối:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'vanho_tourism');
define('DB_USER', 'root');        // Tên user MySQL
define('DB_PASS', '');            // Mật khẩu MySQL
```

### Bước 4: Cấu hình URL

Trong file `includes/config.php`, thay đổi `SITE_URL` cho phù hợp:

```php
define('SITE_URL', 'http://localhost/vanho-tourism');
```

### Bước 5: Phân quyền thư mục uploads

```bash
chmod -R 755 uploads/
```

Trên Windows (XAMPP), thư mục uploads thường đã có quyền ghi.

### Bước 6: Truy cập website

- **Frontend**: `http://localhost/vanho-tourism/`
- **Admin Panel**: `http://localhost/vanho-tourism/admin/`

---

## 🔑 Tài khoản Admin mặc định

| Trường   | Giá trị    |
| -------- | ---------- |
| Username | `admin`    |
| Password | `admin123` |

> ⚠️ **Lưu ý**: Hãy đổi mật khẩu admin sau khi đăng nhập lần đầu!

---

## 📁 Cấu trúc thư mục

```
vanho-tourism/
├── admin/                  # Panel quản trị
│   ├── includes/           # Header, sidebar, footer admin
│   ├── places/             # CRUD địa điểm
│   ├── news/               # CRUD tin tức
│   ├── index.php           # Dashboard
│   ├── login.php           # Đăng nhập
│   ├── logout.php          # Đăng xuất
│   └── contacts.php        # Quản lý liên hệ
│
├── assets/                 # Tài nguyên tĩnh
│   ├── css/
│   │   ├── style.css       # CSS frontend
│   │   └── admin.css       # CSS admin
│   ├── js/
│   │   ├── main.js         # JS frontend
│   │   └── admin.js        # JS admin
│   └── images/             # Ảnh tĩnh
│
├── includes/               # PHP includes
│   ├── config.php          # Cấu hình DB
│   ├── functions.php       # Hàm tiện ích
│   ├── header.php          # Header frontend
│   └── footer.php          # Footer frontend
│
├── uploads/                # Thư mục upload
│   ├── places/             # Ảnh địa điểm
│   └── news/               # Ảnh tin tức
│
├── index.php               # Trang chủ
├── places.php              # Danh sách địa điểm
├── place-detail.php        # Chi tiết địa điểm
├── news.php                # Danh sách tin tức
├── news-detail.php         # Chi tiết tin tức
├── contact.php             # Liên hệ
├── 404.php                 # Trang lỗi 404
├── database.sql            # File SQL
├── .htaccess               # Cấu hình Apache
├── sitemap.xml             # Sitemap SEO
├── robots.txt              # Robots SEO
└── README.md               # File hướng dẫn
```

---

## ✨ Tính năng chính

### Frontend

- ✅ Hero Slider với SwiperJS (fade effect, autoplay)
- ✅ Danh sách địa điểm du lịch với phân trang
- ✅ Chi tiết địa điểm: mô tả, bản đồ, gallery ảnh
- ✅ Danh sách tin tức với phân trang
- ✅ Chi tiết tin tức + bài viết liên quan
- ✅ Form liên hệ với validation
- ✅ Thiết kế Responsive (Mobile-First)
- ✅ Back to Top button
- ✅ Lightbox xem ảnh phóng to
- ✅ Loading animation
- ✅ Scroll animation (fade-in)

### Admin Panel

- ✅ Đăng nhập bảo mật (session + password_hash)
- ✅ Dashboard thống kê
- ✅ CRUD Địa điểm (thêm/sửa/xóa + upload ảnh + gallery)
- ✅ CRUD Tin tức (thêm/sửa/xóa + upload ảnh)
- ✅ Quản lý liên hệ (xem/xóa)
- ✅ Upload ảnh với kiểm tra bảo mật

### Bảo mật

- ✅ PDO Prepared Statements (chống SQL Injection)
- ✅ CSRF Token cho forms
- ✅ XSS Protection (htmlspecialchars)
- ✅ Password hashing (bcrypt)
- ✅ File upload validation
- ✅ Session regeneration

### SEO

- ✅ Meta tags (title, description, keywords)
- ✅ Open Graph tags
- ✅ Schema markup
- ✅ Sitemap.xml
- ✅ Robots.txt
- ✅ Semantic HTML

---

## 🎨 Bảng màu

| Màu        | Mã        | Sử dụng     |
| ---------- | --------- | ----------- |
| Primary    | `#2E7D32` | Xanh lá đậm |
| Secondary  | `#1976D2` | Xanh dương  |
| Accent     | `#FFA726` | Cam nhạt    |
| Text       | `#333333` | Chữ chính   |
| Background | `#F5F5F5` | Nền         |

---

## 📝 Thêm ảnh mẫu

Website sử dụng ảnh từ Unsplash làm placeholder. Để thêm ảnh thực:

1. **Đăng nhập Admin**: `http://localhost/vanho-tourism/admin/`
2. **Quản lý địa điểm** → Sửa từng địa điểm → Upload ảnh
3. **Quản lý tin tức** → Sửa từng bài viết → Upload ảnh
4. Ảnh sẽ được lưu tại thư mục `uploads/`

---

## 📄 License

Dự án được phát triển phục vụ mục đích quảng bá du lịch xã Vân Hồ.

---

_Phát triển bởi đội ngũ Du lịch Vân Hồ © 2024_
