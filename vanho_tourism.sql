-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th3 25, 2026 lúc 08:10 AM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `vanho_tourism`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `contacts`
--

CREATE TABLE `contacts` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `contacts`
--

INSERT INTO `contacts` (`id`, `name`, `email`, `phone`, `message`, `is_read`, `created_at`) VALUES
(1, 'Nguyễn Văn An', 'nguyenan@gmail.com', '0912345678', 'Tôi muốn hỏi thông tin về tour du lịch Vân Hồ 3 ngày 2 đêm cho nhóm 10 người vào tháng 3. Vui lòng tư vấn giúp tôi. Cảm ơn!', 1, '2026-03-03 03:22:05'),
(2, 'Trần Thị Bình', 'binhtt@gmail.com', '0987654321', 'Cho tôi hỏi homestay ở bản Hua Tạt có phòng trống vào cuối tuần này không? Giá phòng bao nhiêu? Có bao gồm bữa ăn không?', 1, '2026-03-03 03:22:05'),
(3, 'Lê Hoàng Minh', 'minhle@gmail.com', '0901234567', 'Website rất đẹp và thông tin hữu ích. Tôi dự định sẽ tổ chức team building cho công ty tại Vân Hồ. Có thể hỗ trợ tổ chức được không?', 1, '2026-03-03 03:22:05'),
(4, 'Thào A Dương', 'duongat.k63cntt-a@utb.edu.vn', '0349229870', 'Tôi muốn đi du lịch rừng thông Bản Áng', 1, '2026-03-03 14:57:49');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `foods`
--

CREATE TABLE `foods` (
  `id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `short_description` text DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `subtitle` varchar(255) DEFAULT NULL,
  `origin` varchar(255) DEFAULT NULL,
  `ethnicity` varchar(255) DEFAULT NULL,
  `spice_level` tinyint(3) unsigned NOT NULL DEFAULT 2,
  `best_season` varchar(100) DEFAULT NULL,
  `rating_value` decimal(2,1) NOT NULL DEFAULT 5.0,
  `ingredients` text DEFAULT NULL,
  `taste_tips` text DEFAULT NULL,
  `where_to_eat` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `views` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `foods`
--

INSERT INTO `foods` (`id`, `name`, `slug`, `short_description`, `description`, `image`, `views`, `created_at`, `updated_at`) VALUES
(1, 'Thịt trâu gác bếp', 'thit-trau-gac-bep', 'Thịt trâu được tẩm ướp gia vị rừng như mắc khén, hạt dổi, gừng, ớt… rồi treo gác bếp trong nhiều tháng. Thịt dai, ngọt, thơm mùi khói; có vị cay nhẹ, đậm đà thường dùng làm món nhắm hoặc quà mang về.', '', '1774347320_bda38738.png', 0, '2026-03-24 09:42:42', '2026-03-24 10:15:20'),
(2, 'Cá suối nướng', 'ca-suoi-nuong', 'Một món ăn giản dị nhưng rất cuốn hút. Cá suối nhỏ được bắt trực tiếp từ các khe suối, làm sạch rồi nướng trên than hồng. Thịt cá chắc, thơm thường ăn kèm muối chẩm chéo giúp giữ trọn vị ngọt tự nhiên.', '', '1774347308_19f1376f.png', 0, '2026-03-24 09:43:25', '2026-03-24 10:15:08'),
(3, 'Gà đồi nướng mắc khén', 'ga-doi-nuong-mac-khen', 'Đây là món ăn thường xuất hiện trong mâm cơm đãi khách. Gà được nuôi thả tự nhiên, thịt săn chắc, ướp gia vị đặc trưng của Tây Bắc. Vị thơm của mắc khén, hạt dổi kết hợp với món gà nướng da giòn, thịt mềm.', '', '1774347219_db580713.png', 0, '2026-03-24 09:43:54', '2026-03-24 10:13:39'),
(4, 'Cơm lam, xôi ngũ sắc', 'com-lam-xoi-ngu-sac', 'Cơm lam được làm từ gạo nếp nướng trong ống tre, dẻo thơm mùi tre nứa trong khi xôi ngũ sắc mang ý nghĩa văn hóa, tượng trưng cho ngũ hành. Hai món ăn dân dã nhưng mang đậm bản sắc vùng cao.', '', '1774347200_9a402b53.png', 0, '2026-03-24 09:44:24', '2026-03-24 10:13:20'),
(5, 'Rượu ngô, rượu táo mèo', 'ruou-ngo-ruou-tao-meo', 'Chén rượu vùng cao nồng ấm là “chất xúc tác” cho những buổi giao lưu. Rượu ngô thơm, dễ uống trong khi rượu táo mèo chua nhẹ, tốt cho tiêu hóa. Thưởng thức vừa đủ để cảm nhận hương vị và không khí bản làng.', '', '1774347186_ab3c94dc.png', 0, '2026-03-24 09:44:36', '2026-03-24 10:13:06');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `homestays`
--

CREATE TABLE `homestays` (
  `id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `short_description` text DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `price_per_night` decimal(12,2) DEFAULT 0.00,
  `max_guests` int(11) NOT NULL DEFAULT 10,
  `check_in_time` varchar(5) DEFAULT NULL,
  `check_out_time` varchar(5) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `views` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `total_rooms` int(11) DEFAULT 1 COMMENT 'Tổng số phòng'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `homestays`
--

INSERT INTO `homestays` (`id`, `name`, `slug`, `short_description`, `description`, `address`, `latitude`, `longitude`, `price_per_night`, `image`, `views`, `created_at`, `updated_at`, `total_rooms`) VALUES
(1, 'Bản Art Stay', 'ban-art-stay', 'Bản Art Stay là một trong những homestay đẹp ở Vân Hồ, thích hợp cho du khách muốn hòa mình với thiên nhiên, muốn kết nối gần hơn với những cảnh đẹp hoang sơ, yên bình. Đây là một quần thể lưu trú tọa lạc trong một thung lũng nhỏ ở xã Chiềng Đi 1, huyện Vân Hồ, cách Hà Nội khoảng 3 tiếng di chuyển.', '<p><strong>Homestay </strong>n&agrave;y vốn l&agrave; một dự &aacute;n cộng đồng do nghệ sỹ dương cầm Ph&oacute; An My kết hợp c&ugrave;ng nh&oacute;m c&aacute;c nghệ sĩ kh&aacute;c thực hiện nhằm lưu giữ lại những gi&aacute; trị truyền thống của nh&agrave; cửa, văn h&oacute;a, nếp sống. Đồng thời hỗ trợ b&agrave; con người M&ocirc;ng, Dao, Th&aacute;i ph&aacute;t triển du lịch. Ở Bản Art Stay c&oacute; nhiều homestay để du kh&aacute;ch lựa chọn, mang lại những trải nghiệm thực sự đ&aacute;ng nhớ.&nbsp;</p>\r\n', 'Xã Chiềng Đi 1, huyện Vân Hồ', NULL, 0.0000001, 300000.00, '1774338134_9a51ca09.jpg', 0, '2026-03-24 07:42:14', '2026-03-24 07:56:28', 1),
(2, 'Gió\'s House', 'gios-house', 'Với vị trí nằm trên lưng chừng đồi, Gió’s House thực sự là chốn bình yên mà bạn có thể tìm về để cân bằng tâm trạng, để mình thực sự được chữa lành giữa muôn vàn áp lực cuộc sống.', '<p>Gi&oacute;&#39;s House l&agrave; một homestay mộc mạc, đơn sơ nằm giữa m&ecirc;nh m&ocirc;ng vườn mận. Mỗi độ xu&acirc;n sang, cả khu vườn nở hoa trắng x&oacute;a, &ocirc;m trọn lấy căn homestay giản dị, y&ecirc;n l&agrave;nh. Cứ gh&eacute; Gi&oacute;&#39;s House, d&ugrave; để nghỉ ngơi hay muốn t&igrave;m một nơi để sống ảo, chắc hẳn du kh&aacute;ch sẽ kh&ocirc;ng thất vọng khi trải nghiệm.</p>\r\n\r\n<p>Khu homestay đẹp ở V&acirc;n Hồ n&agrave;y được thiết kế bề thế v&agrave; chỉn chu, quy m&ocirc; tương đối lớn để đ&aacute;p ứng đa dạng nhu cầu của du kh&aacute;ch. Ph&ograve;ng nghỉ sử dụng chất liệu gỗ chủ đạo, nội thất l&agrave; c&aacute;c gam m&agrave;u ấm &aacute;p đặc trưng của v&ugrave;ng cao. Ph&ograve;ng n&agrave;o cũng c&oacute; cửa sổ nh&igrave;n ra vườn cực kỳ tho&aacute;ng v&agrave; mang lại cảm gi&aacute;c thư th&aacute;i, an y&ecirc;n.&nbsp;</p>\r\n\r\n<p>Khu vườn của Gi&oacute;&#39;s House rất rộng, trồng nhiều c&acirc;y như mận, đ&agrave;o, th&ecirc;m c&aacute;c luống hoa cải, cẩm t&uacute; cầu, hoa hồng,&hellip; t&ocirc; vẽ cho bức tranh th&ecirc;m phần n&ecirc;n thơ, l&atilde;ng mạn. Dừng ch&acirc;n lưu tr&uacute; ở đ&acirc;y, du kh&aacute;ch c&oacute; thể thong thả đi dạo, chụp ảnh, lưu lại cho m&igrave;nh nhiều khoảnh khắc đẹp trong chuyến vi vu V&acirc;n Hồ Sơn La.&nbsp;</p>\r\n', 'Bản Chiềng đi 2, huyện Vân Hồ.', NULL, NULL, 200000.00, '1774338319_f971c2f7.jpg', 1, '2026-03-24 07:45:19', '2026-03-24 07:56:10', 1),
(3, 'A Chu Homestay', 'a-chu-homestay', 'Đến với A Chu Homestay, bạn sẽ thực sự hòa mình vào cuộc sống của người dân vùng cao với những nếp nhà gỗ mái lá mộc mạc mà đầy ấm cúng. Trước hiên nhà trồng đầy những cây đào, cây mận, hoa nở rợp trời mỗi khi mùa xuân đến. Khung cảnh ấy ngọt ngào, lãng mạn và dịu dàng đến nỗi ai đến một lần đều đem lòng thương nhớ.', '<p>B&ecirc;n trong homestay vẫn sử dụng vật liệu gỗ l&agrave;m chủ đạo nhưng c&oacute; th&ecirc;m bồn tắm rộng lớn, c&oacute; cửa k&iacute;nh, c&oacute; r&egrave;m xinh v&ocirc; c&ugrave;ng tinh tế. Đặc biệt, khoảng s&acirc;n rộng quanh homestay c&ograve;n được trồng hoa cải trắng, mỗi m&ugrave;a hoa nở vẽ n&ecirc;n một bức tranh đầy l&atilde;ng mạn cho A Chu Homestay, tạo n&ecirc;n nhiều g&oacute;c sống ảo đẹp cho du kh&aacute;ch.<br />\r\n&nbsp;A Chu Homestay th&iacute;ch hợp cho những du kh&aacute;ch đang muốn đi trốn, muốn được nghỉ ngơi một c&aacute;ch tiện nghi v&agrave; thoải m&aacute;i trong một nếp nh&agrave; xinh đẹp, hiền h&ograve;a. H&atilde;y d&agrave;nh thời gian đọc s&aacute;ch, ngủ một giấc thật ngon v&agrave; thưởng thức những m&oacute;n ăn đặc sắc khi đến A Chu để t&igrave;m cho m&igrave;nh sự c&acirc;n bằng trong t&acirc;m hồn, để hiểu hạnh ph&uacute;c thực sự đến từ những điều nhỏ b&eacute;.</p>\r\n', 'Bản Hua Tạt, huyện Vân Hồ', NULL, NULL, 200000.00, '1774338440_3de6b1cb.jpg', 7, '2026-03-24 07:47:20', '2026-03-25 04:35:13', 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `homestay_bookings`
--

CREATE TABLE `homestay_bookings` (
  `id` int(11) NOT NULL,
  `homestay_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `check_in` date NOT NULL,
  `check_out` date NOT NULL,
  `guests` int(11) DEFAULT 1,
  `total_price` decimal(12,2) DEFAULT 0.00,
  `note` text DEFAULT NULL,
  `status` enum('pending','confirmed','cancelled') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `hold_until` datetime DEFAULT NULL COMMENT 'Thời gian giữ chỗ',
  `payment_status` enum('unpaid','paid','refunded') DEFAULT 'unpaid' COMMENT 'Trạng thái thanh toán'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `homestay_bookings`
--

INSERT INTO `homestay_bookings` (`id`, `homestay_id`, `user_id`, `check_in`, `check_out`, `guests`, `total_price`, `note`, `status`, `created_at`, `updated_at`, `hold_until`, `payment_status`) VALUES
(1, 3, 1, '2026-03-25', '2026-03-29', 2, 800000.00, '', 'confirmed', '2026-03-25 04:29:08', '2026-03-25 04:30:03', NULL, 'unpaid'),
(2, 3, 1, '2026-03-25', '2026-03-28', 2, 600000.00, '', 'pending', '2026-03-25 04:35:13', NULL, NULL, 'unpaid');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `homestay_reviews`
--

CREATE TABLE `homestay_reviews` (
  `id` int(11) NOT NULL,
  `homestay_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` tinyint(4) NOT NULL,
  `content` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `news`
--

CREATE TABLE `news` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `content` longtext DEFAULT NULL,
  `excerpt` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `views` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `news`
--

INSERT INTO `news` (`id`, `title`, `slug`, `content`, `excerpt`, `image`, `views`, `created_at`, `updated_at`) VALUES
(1, 'Lễ hội Hoa Mận Vân Hồ 2026 - Sắc trắng Tây Bắc', 'le-hoi-hoa-man-van-ho-2026-sac-trang-tay-bac', '<p>Lễ hội Hoa Mận V&acirc;n Hồ 2026 sẽ diễn ra từ ng&agrave;y 15/1 đến 20/1/2026 tại bản Hua Tạt, x&atilde; V&acirc;n Hồ, huyện V&acirc;n Hồ, tỉnh Sơn La. Đ&acirc;y l&agrave; sự kiện văn h&oacute;a du lịch thường ni&ecirc;n nhằm quảng b&aacute; vẻ đẹp của m&ugrave;a hoa mận v&agrave; văn h&oacute;a đặc sắc của đồng b&agrave;o c&aacute;c d&acirc;n tộc.</p>\r\n\r\n<p>Lễ hội bao gồm nhiều hoạt động hấp dẫn như: tr&igrave;nh diễn trang phục d&acirc;n tộc, thi nấu ăn truyền thống, c&aacute;c tr&ograve; chơi d&acirc;n gian (n&eacute;m pao, k&eacute;o co, đẩy gậy), v&agrave; đặc biệt l&agrave; chương tr&igrave;nh văn nghệ với c&aacute;c tiết mục m&uacute;a kh&egrave;n M&ocirc;ng.</p>\r\n\r\n<p>Ban tổ chức đ&atilde; chuẩn bị kỹ lưỡng c&aacute;c điều kiện đ&oacute;n tiếp du kh&aacute;ch với hệ thống homestay được n&acirc;ng cấp, c&aacute;c tuyến đường trekking ngắm hoa được đ&aacute;nh dấu r&otilde; r&agrave;ng.</p>\r\n', 'Lễ hội Hoa Mận Vân Hồ 2026 sẽ diễn ra từ ngày 15/1 đến 20/1/2026 tại bản Hua Tạt với nhiều hoạt động văn hóa đặc sắc.', '1774152457_c32baf0c.jpg', 524, '2026-03-03 03:22:05', '2026-03-22 04:07:37'),
(2, 'Top 5 homestay đẹp nhất Vân Hồ cho kỳ nghỉ cuối tuần', 'top-5-homestay-dep-nhat-van-ho-cho-ky-nghi-cuoi-tuan', '<p>V&acirc;n Hồ đang trở th&agrave;nh điểm đến l&yacute; tưởng cho kỳ nghỉ cuối tuần với nhiều homestay chất lượng. Dưới đ&acirc;y l&agrave; 5 homestay được đ&aacute;nh gi&aacute; cao nhất:</p>\r\n\r\n<p><strong>1. Homestay Hua Tạt Garden</strong><br />\r\nNằm ngay trung t&acirc;m bản Hua Tạt, homestay được x&acirc;y dựng theo kiến tr&uacute;c nh&agrave; s&agrave;n truyền thống nhưng c&oacute; đầy đủ tiện nghi hiện đại. View nh&igrave;n ra thung lũng mận tuyệt đẹp.</p>\r\n\r\n<p><strong>2. Piềng Sang Eco Lodge</strong><br />\r\nKhu nghỉ dưỡng sinh th&aacute;i với c&aacute;c bungalow nằm giữa rừng th&ocirc;ng. Đặc biệt ph&ugrave; hợp cho những ai muốn t&igrave;m sự y&ecirc;n tĩnh v&agrave; gần gũi thi&ecirc;n nhi&ecirc;n.</p>\r\n\r\n<p><strong>3. V&acirc;n Hồ Mountain View</strong><br />\r\nTọa lạc tr&ecirc;n đỉnh đồi, homestay c&oacute; view panorama ấn tượng nh&igrave;n ra to&agrave;n cảnh thung lũng v&agrave; những d&atilde;y n&uacute;i tr&ugrave;ng điệp.</p>\r\n\r\n<p><strong>4. Bản &Aacute;ng Retreat</strong><br />\r\nHomestay mang phong c&aacute;ch rustic kết hợp giữa truyền thống v&agrave; hiện đại, nổi tiếng với bữa s&aacute;ng đặc sản địa phương.</p>\r\n\r\n<p><strong>5. Tạt N&agrave;ng Riverside</strong><br />\r\nVị tr&iacute; độc đ&aacute;o b&ecirc;n d&ograve;ng suối, du kh&aacute;ch c&oacute; thể nghe tiếng nước chảy r&oacute;c r&aacute;ch suốt đ&ecirc;m - một trải nghiệm thư gi&atilde;n tuyệt vời.</p>\r\n', 'Khám phá 5 homestay được đánh giá cao nhất tại Vân Hồ, phù hợp cho kỳ nghỉ cuối tuần thư giãn.', '1774152474_10a1b5a4.jpg', 414, '2026-03-03 03:22:05', '2026-03-25 04:26:20'),
(3, 'Hướng dẫn trekking khám phá Thác Tạt Nàng', 'huong-dan-trekking-kham-pha-thac-tat-nang', '<p>Th&aacute;c Tạt N&agrave;ng l&agrave; một trong những điểm trekking hấp dẫn nhất V&acirc;n Hồ. B&agrave;i viết n&agrave;y sẽ hướng dẫn bạn c&aacute;ch chuẩn bị v&agrave; trải nghiệm chuyến trekking an to&agrave;n, th&uacute; vị.</p>\r\n\r\n<p><strong>Thời gian tốt nhất:</strong> Từ th&aacute;ng 4 đến th&aacute;ng 10, khi th&aacute;c nước dồi d&agrave;o nhất. Tr&aacute;nh m&ugrave;a mưa lớn (th&aacute;ng 7-8) v&igrave; đường trơn trượt.</p>\r\n\r\n<p><strong>Chuẩn bị:</strong> Gi&agrave;y trekking chống trượt, &aacute;o kho&aacute;c nhẹ, nước uống (&iacute;t nhất 1.5L), đồ ăn nhẹ, thuốc chống muỗi, kem chống nắng, v&agrave; m&aacute;y ảnh.</p>\r\n\r\n<p><strong>Lộ tr&igrave;nh:</strong> Xuất ph&aacute;t từ bản Hua Tạt, đi theo đường m&ograve;n qua rừng tre, rừng th&ocirc;ng khoảng 3km. Thời gian đi bộ khoảng 1.5-2 giờ t&ugrave;y tốc độ. N&ecirc;n thu&ecirc; hướng dẫn vi&ecirc;n địa phương (200.000đ/người).</p>\r\n\r\n<p><strong>Lưu &yacute; an to&agrave;n:</strong> Lu&ocirc;n đi theo nh&oacute;m, kh&ocirc;ng tự &yacute; bơi nếu nước lớn, giữ g&igrave;n vệ sinh m&ocirc;i trường, kh&ocirc;ng h&aacute;i hoa hoặc bẻ c&agrave;nh c&acirc;y.</p>\r\n', 'Hướng dẫn chi tiết chuẩn bị và trải nghiệm trekking đến Thác Tạt Nàng - từ lộ trình đến lưu ý an toàn.', '1774152484_d148ed97.jpg', 287, '2026-03-03 03:22:05', '2026-03-22 04:08:04'),
(4, 'Ẩm thực Vân Hồ: Những món ăn đặc sản không thể bỏ qua', 'am-thuc-van-ho-nhung-mon-an-dac-san-khong-the-bo-qua', '<p>V&acirc;n Hồ kh&ocirc;ng chỉ hấp dẫn bởi cảnh đẹp m&agrave; c&ograve;n bởi nền ẩm thực phong ph&uacute;, mang đậm hương vị n&uacute;i rừng T&acirc;y Bắc.</p>\r\n\r\n<p><strong>1. Thắng cố</strong><br />\r\nM&oacute;n ăn truyền thống của người M&ocirc;ng, được nấu từ nội tạng v&agrave; thịt ngựa với nhiều loại thảo mộc. Thưởng thức thắng cố trong phi&ecirc;n chợ l&agrave; trải nghiệm văn h&oacute;a độc đ&aacute;o.</p>\r\n\r\n<p><strong>2. Cơm lam</strong><br />\r\nGạo nếp nương được cho v&agrave;o ống tre tươi rồi nướng tr&ecirc;n than hồng. Cơm lam V&acirc;n Hồ c&oacute; hương thơm đặc trưng của nếp nương v&agrave; tre tươi, ăn k&egrave;m muối vừng hoặc thịt nướng.</p>\r\n\r\n<p><strong>3. Pa pỉnh tộp (C&aacute; nướng)</strong><br />\r\nC&aacute; suối tươi được ướp gia vị thảo mộc rồi kẹp nướng tr&ecirc;n than. M&oacute;n ăn thơm lừng, đậm đ&agrave; hương vị n&uacute;i rừng.</p>\r\n\r\n<p><strong>4. Rượu ng&ocirc; men l&aacute;</strong><br />\r\nRượu được nấu từ ng&ocirc; nương với men l&aacute; rừng tự nhi&ecirc;n, c&oacute; vị ngọt nhẹ, thơm m&aacute;t. Đ&acirc;y l&agrave; đặc sản kh&ocirc;ng thể thiếu trong mỗi bữa cơm của người M&ocirc;ng.</p>\r\n\r\n<p><strong>5. Rau rừng x&agrave;o</strong><br />\r\nC&aacute;c loại rau rừng như rau dớn, rau sắng, hoa ban được h&aacute;i từ rừng tự nhi&ecirc;n, chế biến đơn giản nhưng mang hương vị thanh m&aacute;t, đặc biệt.</p>\r\n', 'Khám phá nền ẩm thực phong phú của Vân Hồ với những món ăn đặc sản mang đậm hương vị Tây Bắc.', '1774152497_97db699e.jpg', 356, '2026-03-03 03:22:05', '2026-03-22 04:08:17'),
(5, 'Vân Hồ mùa nào đẹp nhất? Cẩm nang du lịch theo mùa', 'van-ho-mua-nao-dep-nhat-cam-nang-du-lich-theo-mua', '<p>V&acirc;n Hồ đẹp quanh năm nhưng mỗi m&ugrave;a lại mang một vẻ đẹp ri&ecirc;ng. H&atilde;y c&ugrave;ng t&igrave;m hiểu để chọn thời điểm ph&ugrave; hợp nhất cho chuyến đi của bạn.</p>\r\n\r\n<p><strong>M&ugrave;a xu&acirc;n (th&aacute;ng 1-3):</strong> Đ&acirc;y l&agrave; m&ugrave;a đẹp nhất với hoa mận, hoa đ&agrave;o nở trắng hồng khắp c&aacute;c bản l&agrave;ng. Nhiệt độ m&aacute;t mẻ 15-22&deg;C, l&yacute; tưởng cho trekking v&agrave; ngắm cảnh. Đặc biệt, th&aacute;ng 2 c&oacute; lễ hội hoa mận thu h&uacute;t đ&ocirc;ng du kh&aacute;ch.</p>\r\n\r\n<p><strong>M&ugrave;a h&egrave; (th&aacute;ng 4-6):</strong> Thời tiết ấm &aacute;p, ruộng bậc thang bắt đầu đổ nước tạo n&ecirc;n cảnh tượng như gương soi trời. Th&aacute;c nước chảy mạnh, ph&ugrave; hợp trekking v&agrave; tắm th&aacute;c.</p>\r\n\r\n<p><strong>M&ugrave;a thu (th&aacute;ng 7-10):</strong> M&ugrave;a l&uacute;a ch&iacute;n nhuộm v&agrave;ng cả thung lũng. Th&aacute;ng 9-10 l&agrave; thời điểm đẹp nhất để chụp ảnh ruộng bậc thang v&agrave;ng rực. Tuy nhi&ecirc;n, cần lưu &yacute; mưa thường xuy&ecirc;n.</p>\r\n\r\n<p><strong>M&ugrave;a đ&ocirc;ng (th&aacute;ng 11-12):</strong> Sương m&ugrave; giăng khắp c&aacute;c bản l&agrave;ng tạo n&ecirc;n cảnh tượng huyền ảo. Nếu may mắn, bạn c&oacute; thể thấy băng gi&aacute; tr&ecirc;n đỉnh n&uacute;i. Nhiệt độ c&oacute; thể xuống 5-10&deg;C.</p>\r\n', 'Tổng hợp cẩm nang du lịch Vân Hồ theo từng mùa trong năm - giúp bạn chọn thời điểm phù hợp nhất.', '1774152506_023f4645.jpg', 489, '2026-03-03 03:22:05', '2026-03-22 04:08:26');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `content` text DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `payment_method` varchar(50) DEFAULT NULL COMMENT 'VNPAY, MOMO, CASH...',
  `status` enum('pending','success','failed') DEFAULT 'pending',
  `transaction_code` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `places`
--

CREATE TABLE `places` (
  `id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `short_description` text DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `map_embed` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `views` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `places`
--

INSERT INTO `places` (`id`, `name`, `slug`, `short_description`, `description`, `location`, `latitude`, `longitude`, `map_embed`, `image`, `views`, `created_at`, `updated_at`) VALUES
(1, 'Bản Hua Tạt', 'ban-hua-tat', 'Bản làng người Mông nằm trên đỉnh núi cao, nổi tiếng với vẻ đẹp hoang sơ và văn hóa truyền thống đặc sắc.', '<p>Bản Hua Tạt l&agrave; một bản l&agrave;ng người M&ocirc;ng nằm tr&ecirc;n độ cao hơn 1.200m so với mực nước biển, thuộc x&atilde; V&acirc;n Hồ, huyện V&acirc;n Hồ, tỉnh Sơn La. Đ&acirc;y l&agrave; một trong những điểm du lịch cộng đồng hấp dẫn nhất khu vực T&acirc;y Bắc.</p>\r\n\r\n<p>Du kh&aacute;ch đến đ&acirc;y sẽ được trải nghiệm cuộc sống b&igrave;nh dị của đồng b&agrave;o d&acirc;n tộc M&ocirc;ng, tham gia c&aacute;c hoạt động văn h&oacute;a truyền thống như dệt vải lanh, th&ecirc;u thổ cẩm, v&agrave; thưởng thức ẩm thực địa phương.</p>\r\n\r\n<p>Bản Hua Tạt đặc biệt đẹp v&agrave;o m&ugrave;a hoa mận (th&aacute;ng 1-2) v&agrave; m&ugrave;a l&uacute;a ch&iacute;n (th&aacute;ng 9-10), khi to&agrave;n bộ thung lũng được phủ bởi sắc trắng tinh kh&ocirc;i hoặc v&agrave;ng &oacute;ng ả.</p>\r\n', 'Bản Hua Tạt, xã Vân Hồ, huyện Vân Hồ, tỉnh Sơn La', NULL, NULL, '<iframe src=\"https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d14868.793!2d104.809!3d20.745!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMjDCsDQ0JzQyLjAiTiAxMDTCsDQ4JzMyLjQiRQ!5e0!3m2!1svi!2s!4v1\" width=\"600\" height=\"450\" style=\"border:0;\" allowfullscreen=\"\" loading=\"lazy\"></iframe>', '1774151458_0a379849.jpg', 263, '2026-03-03 03:22:05', '2026-03-25 04:20:37'),
(2, 'Cung đường chữ S Vân Hồ', 'cung-duong-chu-s-van-ho', 'Cung đường chữ S Vân Hồ là đoạn đường uốn lượn mềm mại giữa núi rừng Tây Bắc, nổi bật với hình dáng giống chữ S độc đáo, là điểm check-in yêu thích của du khách khi đi qua Sơn La.', '<p>Cung đường chữ S nằm tr&ecirc;n Quốc lộ 6, đoạn qua huyện V&acirc;n Hồ, tỉnh Sơn La, l&agrave; một trong những cung đường đ&egrave;o đẹp v&agrave; ấn tượng nhất khu vực T&acirc;y Bắc. Nh&igrave;n từ tr&ecirc;n cao, con đường uốn lượn mềm mại qua c&aacute;c sườn n&uacute;i tạo th&agrave;nh h&igrave;nh chữ S đặc trưng, mang đến khung cảnh h&ugrave;ng vĩ nhưng cũng rất thơ mộng.</p>\r\n\r\n<p>Hai b&ecirc;n đường l&agrave; n&uacute;i non tr&ugrave;ng điệp, xen lẫn với những thung lũng xanh mướt, nương ng&ocirc;, nương ch&egrave; của người d&acirc;n địa phương. V&agrave;o những ng&agrave;y thời tiết đẹp, du kh&aacute;ch c&oacute; thể chi&ecirc;m ngưỡng biển m&acirc;y bồng bềnh hoặc khung cảnh sương m&ugrave; bao phủ, tạo n&ecirc;n vẻ đẹp huyền ảo đặc trưng của v&ugrave;ng cao.</p>\r\n\r\n<p>Đ&acirc;y kh&ocirc;ng chỉ l&agrave; tuyến đường giao th&ocirc;ng quan trọng nối liền c&aacute;c tỉnh T&acirc;y Bắc m&agrave; c&ograve;n l&agrave; điểm dừng ch&acirc;n l&yacute; tưởng cho những ai y&ecirc;u th&iacute;ch du lịch phượt, chụp ảnh v&agrave; kh&aacute;m ph&aacute; thi&ecirc;n nhi&ecirc;n. Cung đường đặc biệt đẹp v&agrave;o s&aacute;ng sớm v&agrave; chiều ho&agrave;ng h&ocirc;n, khi &aacute;nh s&aacute;ng tạo n&ecirc;n những đường cong nổi bật giữa n&uacute;i rừng.</p>\r\n\r\n<p>Du kh&aacute;ch khi đến đ&acirc;y n&ecirc;n ch&uacute; &yacute; an to&agrave;n giao th&ocirc;ng do địa h&igrave;nh đ&egrave;o dốc, nhiều kh&uacute;c cua gấp, đồng thời c&oacute; thể kết hợp tham quan c&aacute;c điểm du lịch l&acirc;n cận như Mộc Ch&acirc;u, th&aacute;c nước v&agrave; c&aacute;c bản l&agrave;ng d&acirc;n tộc để c&oacute; trải nghiệm trọn vẹn hơn.</p>\r\n', 'Quốc lộ 6, khu vực xã Vân Hồ, tỉnh Sơn La, Việt Nam', NULL, NULL, '<iframe src=\"https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d14868.793!2d104.81!3d20.74!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMjDCsDQ0JzI0LjAiTiAxMDTCsDQ4JzM2LjAiRQ!5e0!3m2!1svi!2s!4v1\" width=\"600\" height=\"450\" style=\"border:0;\" allowfullscreen=\"\" loading=\"lazy\"></iframe>', '1774150964_0f414b6a.jpg', 190, '2026-03-03 03:22:05', '2026-03-22 03:42:47'),
(3, 'Thác Tạt Nàng', 'thac-tat-nang', 'Thác nước hùng vĩ giữa đại ngàn, điểm đến lý tưởng cho những ai yêu thích khám phá thiên nhiên.', '<p>Thác Tạt Nàng là một trong những thác nước đẹp nhất vùng Tây Bắc, nằm ẩn mình giữa đại ngàn xanh thẳm của xã Vân Hồ. Thác có độ cao khoảng 30m, nước chảy quanh năm tạo nên âm thanh vang vọng khắp núi rừng.</p><p>Con đường đến thác đi qua những cánh rừng nguyên sinh với hệ sinh thái phong phú, đa dạng. Đây là hành trình trekking hấp dẫn, phù hợp với những du khách yêu thích mạo hiểm và khám phá.</p><p>Tại chân thác, du khách có thể tắm mát trong làn nước trong veo, mát lạnh - một trải nghiệm khó quên giữa núi rừng Tây Bắc.</p>', 'Xã Vân Hồ, huyện Vân Hồ, tỉnh Sơn La', NULL, NULL, '<iframe src=\"https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d14868.793!2d104.82!3d20.75!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMjDCsDQ1JzAwLjAiTiAxMDTCsDQ5JzEyLjAiRQ!5e0!3m2!1svi!2s!4v1\" width=\"600\" height=\"450\" style=\"border:0;\" allowfullscreen=\"\" loading=\"lazy\"></iframe>', '1772549736_d23ed444.jpg', 313, '2026-03-03 03:22:05', '2026-03-03 14:55:39'),
(4, 'Bản Sáng Tùng', 'ban-sang-tung', 'Bản làng người Dao với nét văn hóa độc đáo, nổi tiếng với nghề thuốc nam truyền thống.', '<p>Bản S&aacute;ng T&ugrave;ng l&agrave; nơi sinh sống của cộng đồng người Dao, nổi tiếng với truyền thống sử dụng thuốc nam chữa bệnh. Đến đ&acirc;y, du kh&aacute;ch sẽ được t&igrave;m hiểu về c&aacute;c b&agrave;i thuốc cổ truyền qu&yacute; gi&aacute; được lưu truyền qua nhiều thế hệ.</p>\r\n\r\n<p>Ngo&agrave;i ra, bản c&ograve;n nổi tiếng với c&aacute;c lễ hội truyền thống như lễ cấp sắc, lễ c&uacute;ng rừng với nhiều nghi thức độc đ&aacute;o, thể hiện đời sống t&acirc;m linh phong ph&uacute; của người Dao.</p>\r\n\r\n<p>Kiến tr&uacute;c nh&agrave; truyền thống, trang phục thổ cẩm đặc sắc v&agrave; c&aacute;c m&oacute;n ăn d&acirc;n tộc l&agrave; những điểm nhấn văn h&oacute;a thu h&uacute;t du kh&aacute;ch.</p>\r\n', 'Bản Sáng Tùng, xã Vân Hồ, huyện Vân Hồ, tỉnh Sơn La', NULL, NULL, '<iframe src=\"https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d14868.793!2d104.80!3d20.73!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMjDCsDQzJzQ4LjAiTiAxMDTCsDQ4JzAwLjAiRQ!5e0!3m2!1svi!2s!4v1\" width=\"600\" height=\"450\" style=\"border:0;\" allowfullscreen=\"\" loading=\"lazy\"></iframe>', '1774151997_c659bc7c.jpg', 146, '2026-03-03 03:22:05', '2026-03-22 03:59:57'),
(5, 'Đồi chè Tân Lập', 'doi-che-tan-lap', 'Đồi chè xanh mướt trải dài, nơi sản xuất trà đặc sản Vân Hồ nổi tiếng.', '<p>Đồi ch&egrave; T&acirc;n Lập l&agrave; một trong những v&ugrave;ng trồng ch&egrave; lớn nhất của x&atilde; V&acirc;n Hồ. Những đồi ch&egrave; xanh mướt trải d&agrave;i t&iacute;t tắp tạo n&ecirc;n cảnh quan tuyệt đẹp, đặc biệt v&agrave;o buổi s&aacute;ng sớm khi sương m&ugrave; vẫn c&ograve;n giăng khắp nơi.</p>\r\n\r\n<p>Ch&egrave; V&acirc;n Hồ được trồng ở độ cao tr&ecirc;n 1.000m, kh&iacute; hậu m&aacute;t mẻ quanh năm n&ecirc;n c&oacute; hương vị đặc biệt thơm ngon. Du kh&aacute;ch c&oacute; thể tham gia trải nghiệm h&aacute;i ch&egrave;, sao ch&egrave; c&ugrave;ng người d&acirc;n địa phương.</p>\r\n\r\n<p>Buổi chiều, ngồi nh&acirc;m nhi t&aacute;ch tr&agrave; n&oacute;ng giữa đồi ch&egrave; b&aacute;t ng&aacute;t l&agrave; một trải nghiệm thư gi&atilde;n tuyệt vời.</p>\r\n', 'Bản Tân Lập, xã Vân Hồ, huyện Vân Hồ, tỉnh Sơn La', NULL, NULL, '<iframe src=\"https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d14868.793!2d104.815!3d20.735!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMjDCsDQ0JzA2LjAiTiAxMDTCsDQ4JzU0LjAiRQ!5e0!3m2!1svi!2s!4v1\" width=\"600\" height=\"450\" style=\"border:0;\" allowfullscreen=\"\" loading=\"lazy\"></iframe>', '1774151949_5af2ca13.jpg', 198, '2026-03-03 03:22:05', '2026-03-22 03:59:09'),
(6, 'Hang Dơi bản Piềng', 'hang-doi-ban-pieng', 'Hang động tự nhiên kỳ vĩ với hệ thống thạch nhũ được hình thành hàng triệu năm.', '<p>Hang Dơi bản Piềng l&agrave; một trong những hang động tự nhi&ecirc;n đẹp nhất khu vực V&acirc;n Hồ. Hang c&oacute; chiều d&agrave;i hơn 500m với hệ thống thạch nhũ mu&ocirc;n h&igrave;nh vạn trạng được tạo h&oacute;a kiến tạo qua h&agrave;ng triệu năm.</p>\r\n\r\n<p>B&ecirc;n trong hang, &aacute;nh s&aacute;ng tự nhi&ecirc;n len lỏi qua c&aacute;c khe đ&aacute; tạo n&ecirc;n những hiệu ứng &aacute;nh s&aacute;ng kỳ ảo. Nhiệt độ trong hang lu&ocirc;n m&aacute;t mẻ, dễ chịu d&ugrave; ngo&agrave;i trời n&oacute;ng bức.</p>\r\n\r\n<p>Hang Dơi gắn liền với nhiều truyền thuyết của đồng b&agrave;o d&acirc;n tộc địa phương, tạo th&ecirc;m sức hấp dẫn cho điểm đến n&agrave;y. Du kh&aacute;ch n&ecirc;n mang theo đ&egrave;n pin v&agrave; gi&agrave;y thể thao khi kh&aacute;m ph&aacute; hang.</p>\r\n', 'Bản Piềng, xã Vân Hồ, huyện Vân Hồ, tỉnh Sơn La', NULL, NULL, '<iframe src=\"https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d14868.793!2d104.795!3d20.755!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMjDCsDQ1JzE4LjAiTiAxMDTCsDQ3JzQyLjAiRQ!5e0!3m2!1svi!2s!4v1\" width=\"600\" height=\"450\" style=\"border:0;\" allowfullscreen=\"\" loading=\"lazy\"></iframe>', '1774151925_c705e458.jpg', 167, '2026-03-03 03:22:05', '2026-03-22 03:58:45');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `place_images`
--

CREATE TABLE `place_images` (
  `id` int(11) NOT NULL,
  `place_id` int(11) NOT NULL,
  `image` varchar(255) NOT NULL,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `place_images`
--

INSERT INTO `place_images` (`id`, `place_id`, `image`, `sort_order`, `created_at`) VALUES
(1, 2, '1774150964_8a68cafe.jpg', 1, '2026-03-22 03:42:44'),
(2, 1, '1774151458_353ea31b.jpg', 1, '2026-03-22 03:50:58'),
(3, 6, '1774151925_1e2e6494.jpg', 1, '2026-03-22 03:58:45'),
(4, 5, '1774151949_0b3b4997.jpg', 1, '2026-03-22 03:59:09'),
(5, 4, '1774151997_2452155a.jpg', 1, '2026-03-22 03:59:57');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `place_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` tinyint(4) NOT NULL,
  `content` text DEFAULT NULL,
  `is_approved` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `reviews`
--

INSERT INTO `reviews` (`id`, `place_id`, `user_id`, `rating`, `content`, `is_approved`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 5, 'Tuyệt vời', 1, '2026-03-25 04:20:10', '2026-03-25 04:27:17');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(120) NOT NULL,
  `email` varchar(120) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `role` enum('user','admin') DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `password`, `phone`, `is_active`, `created_at`, `updated_at`, `role`) VALUES
(1, 'Thào A Dương', 'duongat.k63cntt-a@utb.edu.vn', '$2y$10$akhJtyTVPVjZgk7r81HgVeAJX.kuluWveCqpEKdfYKFFiUPv0RSzW', '012346678', 1, '2026-03-22 02:26:19', '2026-03-25 06:49:40', 'admin');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `wishlists`
--

CREATE TABLE `wishlists` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `place_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `contacts`
--
ALTER TABLE `contacts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_created` (`created_at`),
  ADD KEY `idx_read` (`is_read`);

--
-- Chỉ mục cho bảng `foods`
--
ALTER TABLE `foods`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_food_slug` (`slug`),
  ADD KEY `idx_food_created` (`created_at`);

--
-- Chỉ mục cho bảng `homestays`
--
ALTER TABLE `homestays`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_hs_slug` (`slug`),
  ADD KEY `idx_hs_created` (`created_at`);

--
-- Chỉ mục cho bảng `homestay_bookings`
--
ALTER TABLE `homestay_bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_booking_hs` (`homestay_id`),
  ADD KEY `idx_booking_user` (`user_id`),
  ADD KEY `idx_booking_status` (`status`),
  ADD KEY `idx_booking_date` (`homestay_id`,`check_in`,`check_out`);

--
-- Chỉ mục cho bảng `homestay_reviews`
--
ALTER TABLE `homestay_reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `homestay_id` (`homestay_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Chỉ mục cho bảng `news`
--
ALTER TABLE `news`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_slug` (`slug`),
  ADD KEY `idx_created` (`created_at`);

--
-- Chỉ mục cho bảng `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Chỉ mục cho bảng `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booking_id` (`booking_id`);

--
-- Chỉ mục cho bảng `places`
--
ALTER TABLE `places`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_slug` (`slug`),
  ADD KEY `idx_created` (`created_at`);

--
-- Chỉ mục cho bảng `place_images`
--
ALTER TABLE `place_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_place` (`place_id`);

--
-- Chỉ mục cho bảng `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_reviews_place` (`place_id`),
  ADD KEY `idx_reviews_user` (`user_id`),
  ADD KEY `idx_reviews_approved` (`is_approved`);

--
-- Chỉ mục cho bảng `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_users_created` (`created_at`);

--
-- Chỉ mục cho bảng `wishlists`
--
ALTER TABLE `wishlists`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`,`place_id`),
  ADD KEY `place_id` (`place_id`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `contacts`
--
ALTER TABLE `contacts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT cho bảng `foods`
--
ALTER TABLE `foods`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT cho bảng `homestays`
--
ALTER TABLE `homestays`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `homestay_bookings`
--
ALTER TABLE `homestay_bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT cho bảng `homestay_reviews`
--
ALTER TABLE `homestay_reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `news`
--
ALTER TABLE `news`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT cho bảng `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `places`
--
ALTER TABLE `places`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT cho bảng `place_images`
--
ALTER TABLE `place_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT cho bảng `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT cho bảng `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT cho bảng `wishlists`
--
ALTER TABLE `wishlists`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `homestay_bookings`
--
ALTER TABLE `homestay_bookings`
  ADD CONSTRAINT `homestay_bookings_ibfk_1` FOREIGN KEY (`homestay_id`) REFERENCES `homestays` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `homestay_bookings_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `homestay_reviews`
--
ALTER TABLE `homestay_reviews`
  ADD CONSTRAINT `homestay_reviews_ibfk_1` FOREIGN KEY (`homestay_id`) REFERENCES `homestays` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `homestay_reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `homestay_bookings` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `place_images`
--
ALTER TABLE `place_images`
  ADD CONSTRAINT `place_images_ibfk_1` FOREIGN KEY (`place_id`) REFERENCES `places` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`place_id`) REFERENCES `places` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `wishlists`
--
ALTER TABLE `wishlists`
  ADD CONSTRAINT `wishlists_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `wishlists_ibfk_2` FOREIGN KEY (`place_id`) REFERENCES `places` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
