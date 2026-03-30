-- Migration: Add detailed fields for food and homestay detail pages
-- Date: 2026-03-30

ALTER TABLE foods
    ADD COLUMN IF NOT EXISTS subtitle VARCHAR(255) NULL AFTER description,
    ADD COLUMN IF NOT EXISTS origin VARCHAR(255) NULL AFTER subtitle,
    ADD COLUMN IF NOT EXISTS ethnicity VARCHAR(255) NULL AFTER origin,
    ADD COLUMN IF NOT EXISTS spice_level TINYINT UNSIGNED NOT NULL DEFAULT 2 AFTER ethnicity,
    ADD COLUMN IF NOT EXISTS best_season VARCHAR(100) NULL AFTER spice_level,
    ADD COLUMN IF NOT EXISTS rating_value DECIMAL(2,1) NOT NULL DEFAULT 5.0 AFTER best_season,
    ADD COLUMN IF NOT EXISTS ingredients TEXT NULL AFTER rating_value,
    ADD COLUMN IF NOT EXISTS taste_tips TEXT NULL AFTER ingredients,
    ADD COLUMN IF NOT EXISTS where_to_eat TEXT NULL AFTER taste_tips;

ALTER TABLE homestays
    ADD COLUMN IF NOT EXISTS max_guests INT NOT NULL DEFAULT 10 AFTER price_per_night,
    ADD COLUMN IF NOT EXISTS check_in_time VARCHAR(5) NULL AFTER max_guests,
    ADD COLUMN IF NOT EXISTS check_out_time VARCHAR(5) NULL AFTER check_in_time;

UPDATE foods
SET
    subtitle = COALESCE(NULLIF(subtitle, ''), 'Đặc sản Vân Hồ · Sơn La'),
    origin = COALESCE(NULLIF(origin, ''), 'Vân Hồ, Sơn La'),
    ethnicity = COALESCE(NULLIF(ethnicity, ''), 'Thái · Mường'),
    spice_level = COALESCE(spice_level, 2),
    best_season = COALESCE(NULLIF(best_season, ''), 'Tháng 10 - 3'),
    rating_value = COALESCE(rating_value, 5.0),
    ingredients = COALESCE(NULLIF(ingredients, ''), 'Nguyên liệu tươi từ núi rừng Tây Bắc\nGia vị truyền thống của người Thái\nLá rừng đặc trưng vùng Vân Hồ\nRau sạch vùng cao không thuốc trừ sâu\nThịt gia súc nuôi thả tự nhiên\nHạt tiêu rừng Sơn La'),
    taste_tips = COALESCE(NULLIF(taste_tips, ''), 'Thưởng thức vào buổi sáng sớm khi sương mù còn phủ kín núi để cảm nhận trọn vị.\nKết hợp cùng rượu ngô Vân Hồ hoặc nước lá rừng để cân bằng vị đậm của món ăn.\nĐặt tại nhà hàng hoặc homestay địa phương để có công thức truyền thống chuẩn vị.\nĐi chợ phiên cuối tuần để mua nguyên liệu tươi và trải nghiệm văn hóa bản địa.'),
    where_to_eat = COALESCE(NULLIF(where_to_eat, ''), 'Chợ phiên Vân Hồ|T7 & CN\nNhà hàng Bản Mường|TT. Vân Hồ\nHomestay Pa Co|Bản Pa Co');

UPDATE homestays
SET
    max_guests = COALESCE(NULLIF(max_guests, 0), 10),
    check_in_time = COALESCE(NULLIF(check_in_time, ''), '14:00'),
    check_out_time = COALESCE(NULLIF(check_out_time, ''), '12:00');
