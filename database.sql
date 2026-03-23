-- =====================================================
-- DATABASE: Du lịch Vân Hồ - Sơn La
-- Tạo database và dữ liệu mẫu
-- =====================================================

CREATE DATABASE IF NOT EXISTS vanho_tourism 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE vanho_tourism;

-- =====================================================
-- BẢNG: admins - Quản trị viên
-- =====================================================
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- BẢNG: places - Địa điểm du lịch
-- =====================================================
CREATE TABLE IF NOT EXISTS places (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    slug VARCHAR(255) UNIQUE,
    short_description TEXT,
    description LONGTEXT,
    location VARCHAR(255),
    latitude DECIMAL(10,7) NULL,
    longitude DECIMAL(10,7) NULL,
    map_embed TEXT,
    image VARCHAR(255),
    views INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_slug (slug),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- BẢNG: foods - Ẩm thực đặc trưng
-- =====================================================
CREATE TABLE IF NOT EXISTS foods (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    slug VARCHAR(255) UNIQUE,
    short_description TEXT,
    description LONGTEXT,
    image VARCHAR(255),
    views INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_food_slug (slug),
    INDEX idx_food_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- BẢNG: homestays - Homestay
-- =====================================================
CREATE TABLE IF NOT EXISTS homestays (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    slug VARCHAR(255) UNIQUE,
    short_description TEXT,
    description LONGTEXT,
    address VARCHAR(255),
    latitude DECIMAL(10,7) NULL,
    longitude DECIMAL(10,7) NULL,
    price_per_night DECIMAL(12,2) DEFAULT 0,
    image VARCHAR(255),
    views INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_hs_slug (slug),
    INDEX idx_hs_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- BẢNG: users - Người dùng website
-- =====================================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(120) NOT NULL,
    email VARCHAR(120) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_users_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- BẢNG: reviews - Đánh giá địa điểm
-- =====================================================
CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    place_id INT NOT NULL,
    user_id INT NOT NULL,
    rating TINYINT NOT NULL,
    content TEXT,
    is_approved TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (place_id) REFERENCES places(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_reviews_place (place_id),
    INDEX idx_reviews_user (user_id),
    INDEX idx_reviews_approved (is_approved)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- BẢNG: homestay_bookings - Đặt homestay
-- =====================================================
CREATE TABLE IF NOT EXISTS homestay_bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    homestay_id INT NOT NULL,
    user_id INT NOT NULL,
    check_in DATE NOT NULL,
    check_out DATE NOT NULL,
    guests INT DEFAULT 1,
    total_price DECIMAL(12,2) DEFAULT 0,
    note TEXT,
    status ENUM('pending','confirmed','cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (homestay_id) REFERENCES homestays(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_booking_hs (homestay_id),
    INDEX idx_booking_user (user_id),
    INDEX idx_booking_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- BẢNG: place_images - Ảnh gallery của địa điểm
-- =====================================================
CREATE TABLE IF NOT EXISTS place_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    place_id INT NOT NULL,
    image VARCHAR(255) NOT NULL,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (place_id) REFERENCES places(id) ON DELETE CASCADE,
    INDEX idx_place (place_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- BẢNG: news - Tin tức / Bài viết
-- =====================================================
CREATE TABLE IF NOT EXISTS news (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE,
    content LONGTEXT,
    excerpt TEXT,
    image VARCHAR(255),
    views INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_slug (slug),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- BẢNG: contacts - Liên hệ từ khách
-- =====================================================
CREATE TABLE IF NOT EXISTS contacts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_created (created_at),
    INDEX idx_read (is_read)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- DỮ LIỆU MẪU
-- =====================================================

-- Admin (password: admin123)
INSERT INTO admins (username, password, email) VALUES 
('admin', '$2y$10$8K1p/a0dL1LXMIgoEDFrwOfMQbLgMQHp0p5GShSzOQYr7iMRlYIG.', 'admin@vanho.vn');

-- Địa điểm du lịch mẫu
INSERT INTO places (name, slug, short_description, description, location, map_embed, image, views) VALUES 
(
    'Bản Hua Tạt',
    'ban-hua-tat',
    'Bản làng người Mông nằm trên đỉnh núi cao, nổi tiếng với vẻ đẹp hoang sơ và văn hóa truyền thống đặc sắc.',
    '<p>Bản Hua Tạt là một bản làng người Mông nằm trên độ cao hơn 1.200m so với mực nước biển, thuộc xã Vân Hồ, huyện Vân Hồ, tỉnh Sơn La. Đây là một trong những điểm du lịch cộng đồng hấp dẫn nhất khu vực Tây Bắc.</p><p>Du khách đến đây sẽ được trải nghiệm cuộc sống bình dị của đồng bào dân tộc Mông, tham gia các hoạt động văn hóa truyền thống như dệt vải lanh, thêu thổ cẩm, và thưởng thức ẩm thực địa phương.</p><p>Bản Hua Tạt đặc biệt đẹp vào mùa hoa mận (tháng 1-2) và mùa lúa chín (tháng 9-10), khi toàn bộ thung lũng được phủ bởi sắc trắng tinh khôi hoặc vàng óng ả.</p>',
    'Bản Hua Tạt, xã Vân Hồ, huyện Vân Hồ, tỉnh Sơn La',
    '<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d14868.793!2d104.809!3d20.745!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMjDCsDQ0JzQyLjAiTiAxMDTCsDQ4JzMyLjQiRQ!5e0!3m2!1svi!2s!4v1" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy"></iframe>',
    '',
    256
),
(
    'Rừng thông bản Áng',
    'rung-thong-ban-ang',
    'Khu rừng thông cổ thụ hàng trăm tuổi với không khí trong lành, lý tưởng cho hoạt động picnic và cắm trại.',
    '<p>Rừng thông bản Áng là một trong những điểm đến không thể bỏ qua khi tới Vân Hồ. Khu rừng thông rộng lớn với những cây thông cổ thụ hàng trăm năm tuổi tạo nên một không gian xanh mát, trong lành.</p><p>Đây là địa điểm lý tưởng cho các hoạt động ngoài trời như cắm trại, picnic, chụp ảnh. Dưới tán thông, bạn có thể nghe tiếng gió rì rào, thưởng thức không khí mát mẻ quanh năm.</p><p>Rừng thông bản Áng còn là nơi sinh sống của nhiều loài chim quý, phù hợp cho những ai yêu thích quan sát chim và thiên nhiên hoang dã.</p>',
    'Bản Áng, xã Vân Hồ, huyện Vân Hồ, tỉnh Sơn La',
    '<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d14868.793!2d104.81!3d20.74!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMjDCsDQ0JzI0LjAiTiAxMDTCsDQ4JzM2LjAiRQ!5e0!3m2!1svi!2s!4v1" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy"></iframe>',
    '',
    189
),
(
    'Thác Tạt Nàng',
    'thac-tat-nang',
    'Thác nước hùng vĩ giữa đại ngàn, điểm đến lý tưởng cho những ai yêu thích khám phá thiên nhiên.',
    '<p>Thác Tạt Nàng là một trong những thác nước đẹp nhất vùng Tây Bắc, nằm ẩn mình giữa đại ngàn xanh thẳm của xã Vân Hồ. Thác có độ cao khoảng 30m, nước chảy quanh năm tạo nên âm thanh vang vọng khắp núi rừng.</p><p>Con đường đến thác đi qua những cánh rừng nguyên sinh với hệ sinh thái phong phú, đa dạng. Đây là hành trình trekking hấp dẫn, phù hợp với những du khách yêu thích mạo hiểm và khám phá.</p><p>Tại chân thác, du khách có thể tắm mát trong làn nước trong veo, mát lạnh - một trải nghiệm khó quên giữa núi rừng Tây Bắc.</p>',
    'Xã Vân Hồ, huyện Vân Hồ, tỉnh Sơn La',
    '<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d14868.793!2d104.82!3d20.75!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMjDCsDQ1JzAwLjAiTiAxMDTCsDQ5JzEyLjAiRQ!5e0!3m2!1svi!2s!4v1" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy"></iframe>',
    '',
    312
),
(
    'Bản Sáng Tùng',
    'ban-sang-tung',
    'Bản làng người Dao với nét văn hóa độc đáo, nổi tiếng với nghề thuốc nam truyền thống.',
    '<p>Bản Sáng Tùng là nơi sinh sống của cộng đồng người Dao, nổi tiếng với truyền thống sử dụng thuốc nam chữa bệnh. Đến đây, du khách sẽ được tìm hiểu về các bài thuốc cổ truyền quý giá được lưu truyền qua nhiều thế hệ.</p><p>Ngoài ra, bản còn nổi tiếng với các lễ hội truyền thống như lễ cấp sắc, lễ cúng rừng với nhiều nghi thức độc đáo, thể hiện đời sống tâm linh phong phú của người Dao.</p><p>Kiến trúc nhà truyền thống, trang phục thổ cẩm đặc sắc và các món ăn dân tộc là những điểm nhấn văn hóa thu hút du khách.</p>',
    'Bản Sáng Tùng, xã Vân Hồ, huyện Vân Hồ, tỉnh Sơn La',
    '<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d14868.793!2d104.80!3d20.73!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMjDCsDQzJzQ4LjAiTiAxMDTCsDQ4JzAwLjAiRQ!5e0!3m2!1svi!2s!4v1" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy"></iframe>',
    '',
    145
),
(
    'Đồi chè Tân Lập',
    'doi-che-tan-lap',
    'Đồi chè xanh mướt trải dài, nơi sản xuất trà đặc sản Vân Hồ nổi tiếng.',
    '<p>Đồi chè Tân Lập là một trong những vùng trồng chè lớn nhất của xã Vân Hồ. Những đồi chè xanh mướt trải dài tít tắp tạo nên cảnh quan tuyệt đẹp, đặc biệt vào buổi sáng sớm khi sương mù vẫn còn giăng khắp nơi.</p><p>Chè Vân Hồ được trồng ở độ cao trên 1.000m, khí hậu mát mẻ quanh năm nên có hương vị đặc biệt thơm ngon. Du khách có thể tham gia trải nghiệm hái chè, sao chè cùng người dân địa phương.</p><p>Buổi chiều, ngồi nhâm nhi tách trà nóng giữa đồi chè bát ngát là một trải nghiệm thư giãn tuyệt vời.</p>',
    'Bản Tân Lập, xã Vân Hồ, huyện Vân Hồ, tỉnh Sơn La',
    '<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d14868.793!2d104.815!3d20.735!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMjDCsDQ0JzA2LjAiTiAxMDTCsDQ4JzU0LjAiRQ!5e0!3m2!1svi!2s!4v1" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy"></iframe>',
    '',
    198
),
(
    'Hang Dơi bản Piềng',
    'hang-doi-ban-pieng',
    'Hang động tự nhiên kỳ vĩ với hệ thống thạch nhũ được hình thành hàng triệu năm.',
    '<p>Hang Dơi bản Piềng là một trong những hang động tự nhiên đẹp nhất khu vực Vân Hồ. Hang có chiều dài hơn 500m với hệ thống thạch nhũ muôn hình vạn trạng được tạo hóa kiến tạo qua hàng triệu năm.</p><p>Bên trong hang, ánh sáng tự nhiên len lỏi qua các khe đá tạo nên những hiệu ứng ánh sáng kỳ ảo. Nhiệt độ trong hang luôn mát mẻ, dễ chịu dù ngoài trời nóng bức.</p><p>Hang Dơi gắn liền với nhiều truyền thuyết của đồng bào dân tộc địa phương, tạo thêm sức hấp dẫn cho điểm đến này. Du khách nên mang theo đèn pin và giày thể thao khi khám phá hang.</p>',
    'Bản Piềng, xã Vân Hồ, huyện Vân Hồ, tỉnh Sơn La',
    '<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d14868.793!2d104.795!3d20.755!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMjDCsDQ1JzE4LjAiTiAxMDTCsDQ3JzQyLjAiRQ!5e0!3m2!1svi!2s!4v1" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy"></iframe>',
    '',
    167
);

-- Tin tức mẫu
INSERT INTO news (title, slug, content, excerpt, image, views) VALUES 
(
    'Lễ hội Hoa Mận Vân Hồ 2026 - Sắc trắng Tây Bắc',
    'le-hoi-hoa-man-van-ho-2026',
    '<p>Lễ hội Hoa Mận Vân Hồ 2026 sẽ diễn ra từ ngày 15/1 đến 20/1/2026 tại bản Hua Tạt, xã Vân Hồ, huyện Vân Hồ, tỉnh Sơn La. Đây là sự kiện văn hóa du lịch thường niên nhằm quảng bá vẻ đẹp của mùa hoa mận và văn hóa đặc sắc của đồng bào các dân tộc.</p><p>Lễ hội bao gồm nhiều hoạt động hấp dẫn như: trình diễn trang phục dân tộc, thi nấu ăn truyền thống, các trò chơi dân gian (ném pao, kéo co, đẩy gậy), và đặc biệt là chương trình văn nghệ với các tiết mục múa khèn Mông.</p><p>Ban tổ chức đã chuẩn bị kỹ lưỡng các điều kiện đón tiếp du khách với hệ thống homestay được nâng cấp, các tuyến đường trekking ngắm hoa được đánh dấu rõ ràng.</p>',
    'Lễ hội Hoa Mận Vân Hồ 2026 sẽ diễn ra từ ngày 15/1 đến 20/1/2026 tại bản Hua Tạt với nhiều hoạt động văn hóa đặc sắc.',
    '',
    523
),
(
    'Top 5 homestay đẹp nhất Vân Hồ cho kỳ nghỉ cuối tuần',
    'top-5-homestay-dep-nhat-van-ho',
    '<p>Vân Hồ đang trở thành điểm đến lý tưởng cho kỳ nghỉ cuối tuần với nhiều homestay chất lượng. Dưới đây là 5 homestay được đánh giá cao nhất:</p><p><strong>1. Homestay Hua Tạt Garden</strong><br>Nằm ngay trung tâm bản Hua Tạt, homestay được xây dựng theo kiến trúc nhà sàn truyền thống nhưng có đầy đủ tiện nghi hiện đại. View nhìn ra thung lũng mận tuyệt đẹp.</p><p><strong>2. Piềng Sang Eco Lodge</strong><br>Khu nghỉ dưỡng sinh thái với các bungalow nằm giữa rừng thông. Đặc biệt phù hợp cho những ai muốn tìm sự yên tĩnh và gần gũi thiên nhiên.</p><p><strong>3. Vân Hồ Mountain View</strong><br>Tọa lạc trên đỉnh đồi, homestay có view panorama ấn tượng nhìn ra toàn cảnh thung lũng và những dãy núi trùng điệp.</p><p><strong>4. Bản Áng Retreat</strong><br>Homestay mang phong cách rustic kết hợp giữa truyền thống và hiện đại, nổi tiếng với bữa sáng đặc sản địa phương.</p><p><strong>5. Tạt Nàng Riverside</strong><br>Vị trí độc đáo bên dòng suối, du khách có thể nghe tiếng nước chảy róc rách suốt đêm - một trải nghiệm thư giãn tuyệt vời.</p>',
    'Khám phá 5 homestay được đánh giá cao nhất tại Vân Hồ, phù hợp cho kỳ nghỉ cuối tuần thư giãn.',
    '',
    412
),
(
    'Hướng dẫn trekking khám phá Thác Tạt Nàng',
    'huong-dan-trekking-thac-tat-nang',
    '<p>Thác Tạt Nàng là một trong những điểm trekking hấp dẫn nhất Vân Hồ. Bài viết này sẽ hướng dẫn bạn cách chuẩn bị và trải nghiệm chuyến trekking an toàn, thú vị.</p><p><strong>Thời gian tốt nhất:</strong> Từ tháng 4 đến tháng 10, khi thác nước dồi dào nhất. Tránh mùa mưa lớn (tháng 7-8) vì đường trơn trượt.</p><p><strong>Chuẩn bị:</strong> Giày trekking chống trượt, áo khoác nhẹ, nước uống (ít nhất 1.5L), đồ ăn nhẹ, thuốc chống muỗi, kem chống nắng, và máy ảnh.</p><p><strong>Lộ trình:</strong> Xuất phát từ bản Hua Tạt, đi theo đường mòn qua rừng tre, rừng thông khoảng 3km. Thời gian đi bộ khoảng 1.5-2 giờ tùy tốc độ. Nên thuê hướng dẫn viên địa phương (200.000đ/người).</p><p><strong>Lưu ý an toàn:</strong> Luôn đi theo nhóm, không tự ý bơi nếu nước lớn, giữ gìn vệ sinh môi trường, không hái hoa hoặc bẻ cành cây.</p>',
    'Hướng dẫn chi tiết chuẩn bị và trải nghiệm trekking đến Thác Tạt Nàng - từ lộ trình đến lưu ý an toàn.',
    '',
    287
),
(
    'Ẩm thực Vân Hồ: Những món ăn đặc sản không thể bỏ qua',
    'am-thuc-van-ho-mon-an-dac-san',
    '<p>Vân Hồ không chỉ hấp dẫn bởi cảnh đẹp mà còn bởi nền ẩm thực phong phú, mang đậm hương vị núi rừng Tây Bắc.</p><p><strong>1. Thắng cố</strong><br>Món ăn truyền thống của người Mông, được nấu từ nội tạng và thịt ngựa với nhiều loại thảo mộc. Thưởng thức thắng cố trong phiên chợ là trải nghiệm văn hóa độc đáo.</p><p><strong>2. Cơm lam</strong><br>Gạo nếp nương được cho vào ống tre tươi rồi nướng trên than hồng. Cơm lam Vân Hồ có hương thơm đặc trưng của nếp nương và tre tươi, ăn kèm muối vừng hoặc thịt nướng.</p><p><strong>3. Pa pỉnh tộp (Cá nướng)</strong><br>Cá suối tươi được ướp gia vị thảo mộc rồi kẹp nướng trên than. Món ăn thơm lừng, đậm đà hương vị núi rừng.</p><p><strong>4. Rượu ngô men lá</strong><br>Rượu được nấu từ ngô nương với men lá rừng tự nhiên, có vị ngọt nhẹ, thơm mát. Đây là đặc sản không thể thiếu trong mỗi bữa cơm của người Mông.</p><p><strong>5. Rau rừng xào</strong><br>Các loại rau rừng như rau dớn, rau sắng, hoa ban được hái từ rừng tự nhiên, chế biến đơn giản nhưng mang hương vị thanh mát, đặc biệt.</p>',
    'Khám phá nền ẩm thực phong phú của Vân Hồ với những món ăn đặc sản mang đậm hương vị Tây Bắc.',
    '',
    356
),
(
    'Vân Hồ mùa nào đẹp nhất? Cẩm nang du lịch theo mùa',
    'van-ho-mua-nao-dep-nhat',
    '<p>Vân Hồ đẹp quanh năm nhưng mỗi mùa lại mang một vẻ đẹp riêng. Hãy cùng tìm hiểu để chọn thời điểm phù hợp nhất cho chuyến đi của bạn.</p><p><strong>Mùa xuân (tháng 1-3):</strong> Đây là mùa đẹp nhất với hoa mận, hoa đào nở trắng hồng khắp các bản làng. Nhiệt độ mát mẻ 15-22°C, lý tưởng cho trekking và ngắm cảnh. Đặc biệt, tháng 2 có lễ hội hoa mận thu hút đông du khách.</p><p><strong>Mùa hè (tháng 4-6):</strong> Thời tiết ấm áp, ruộng bậc thang bắt đầu đổ nước tạo nên cảnh tượng như gương soi trời. Thác nước chảy mạnh, phù hợp trekking và tắm thác.</p><p><strong>Mùa thu (tháng 7-10):</strong> Mùa lúa chín nhuộm vàng cả thung lũng. Tháng 9-10 là thời điểm đẹp nhất để chụp ảnh ruộng bậc thang vàng rực. Tuy nhiên, cần lưu ý mưa thường xuyên.</p><p><strong>Mùa đông (tháng 11-12):</strong> Sương mù giăng khắp các bản làng tạo nên cảnh tượng huyền ảo. Nếu may mắn, bạn có thể thấy băng giá trên đỉnh núi. Nhiệt độ có thể xuống 5-10°C.</p>',
    'Tổng hợp cẩm nang du lịch Vân Hồ theo từng mùa trong năm - giúp bạn chọn thời điểm phù hợp nhất.',
    '',
    489
);

-- Liên hệ mẫu
INSERT INTO contacts (name, email, phone, message, is_read) VALUES 
('Nguyễn Văn An', 'nguyenan@gmail.com', '0912345678', 'Tôi muốn hỏi thông tin về tour du lịch Vân Hồ 3 ngày 2 đêm cho nhóm 10 người vào tháng 3. Vui lòng tư vấn giúp tôi. Cảm ơn!', 1),
('Trần Thị Bình', 'binhtt@gmail.com', '0987654321', 'Cho tôi hỏi homestay ở bản Hua Tạt có phòng trống vào cuối tuần này không? Giá phòng bao nhiêu? Có bao gồm bữa ăn không?', 0),
('Lê Hoàng Minh', 'minhle@gmail.com', '0901234567', 'Website rất đẹp và thông tin hữu ích. Tôi dự định sẽ tổ chức team building cho công ty tại Vân Hồ. Có thể hỗ trợ tổ chức được không?', 0);

-- Đồng bộ tọa độ cho địa điểm mẫu
UPDATE places SET latitude = 20.7450000, longitude = 104.8090000 WHERE slug = 'ban-hua-tat';
UPDATE places SET latitude = 20.7400000, longitude = 104.8100000 WHERE slug = 'rung-thong-ban-ang';
UPDATE places SET latitude = 20.7500000, longitude = 104.8200000 WHERE slug = 'thac-tat-nang';
UPDATE places SET latitude = 20.7300000, longitude = 104.8000000 WHERE slug = 'ban-sang-tung';
UPDATE places SET latitude = 20.7350000, longitude = 104.8150000 WHERE slug = 'doi-che-tan-lap';
UPDATE places SET latitude = 20.7550000, longitude = 104.7950000 WHERE slug = 'hang-doi-ban-pieng';

-- Ẩm thực mẫu
INSERT INTO foods (name, slug, short_description, description, image, views) VALUES
('Thắng cố Mông', 'thang-co-mong', 'Món ăn truyền thống nổi tiếng của người Mông, đậm vị núi rừng.', '<p>Thắng cố là món ăn truyền thống đặc trưng của đồng bào Mông tại Vân Hồ. Món ăn được ninh lâu với nhiều loại gia vị bản địa.</p><p>Thời điểm thưởng thức ngon nhất là tại các phiên chợ vùng cao.</p>', '', 150),
('Cơm lam nếp nương', 'com-lam-nep-nuong', 'Gạo nếp nương nướng trong ống tre, thơm dẻo đặc trưng.', '<p>Cơm lam được làm từ gạo nếp nương ngon, nướng bằng ống tre tươi và ăn kèm muối vừng hoặc thịt nướng.</p>', '', 132),
('Pa pỉnh tộp', 'pa-pinh-top', 'Cá suối nướng kiểu Thái với gia vị mắc khén và rau thơm.', '<p>Pa pỉnh tộp là món cá nướng trứ danh Tây Bắc, nổi bật bởi hương vị mắc khén và hạt dổi.</p>', '', 167);

-- Homestay mẫu
INSERT INTO homestays (name, slug, short_description, description, address, latitude, longitude, price_per_night, image, views) VALUES
('Hua Tat Valley Homestay', 'hua-tat-valley-homestay', 'Nhà sàn truyền thống view thung lũng mận, không gian yên bình.', '<p>Homestay mang kiến trúc nhà sàn bản địa, phù hợp nghỉ dưỡng gia đình và nhóm bạn.</p>', 'Bản Hua Tạt, xã Vân Hồ, Sơn La', 20.7448000, 104.8095000, 650000, '', 98),
('Van Ho Pine Retreat', 'van-ho-pine-retreat', 'Không gian giữa rừng thông, phù hợp cặp đôi và nhóm nhỏ.', '<p>Nằm gần rừng thông bản Áng, khí hậu mát mẻ quanh năm, có dịch vụ BBQ ngoài trời.</p>', 'Bản Áng, xã Vân Hồ, Sơn La', 20.7399000, 104.8102000, 820000, '', 124),
('Tat Nang Riverside Stay', 'tat-nang-riverside-stay', 'Homestay cạnh suối, tiện trekking thác Tạt Nàng.', '<p>Lựa chọn phù hợp cho du khách yêu khám phá thiên nhiên và trekking.</p>', 'Xã Vân Hồ, Sơn La', 20.7497000, 104.8204000, 540000, '', 87);

-- User mẫu (password: 123456)
INSERT INTO users (full_name, email, password, phone, is_active) VALUES
('Khách Du Lịch', 'user@vanho.vn', '$2y$10$4VQeLmz6l6Ep4sE6h0iu6eW8j6dVzv0rRIjIY45FzSL4A9D5fD8cW', '0988000111', 1);

-- Đánh giá mẫu
INSERT INTO reviews (place_id, user_id, rating, content, is_approved) VALUES
(1, 1, 5, 'Bản Hua Tạt rất đẹp, người dân thân thiện và cảnh hoa mận tuyệt vời.', 1),
(3, 1, 4, 'Thác đẹp và mát, đường đi hơi trơn nên cần giày tốt.', 1);
