-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th5 14, 2026 lúc 12:50 PM
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
-- Cơ sở dữ liệu: `pets_accessories`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `banners`
--

CREATE TABLE `banners` (
  `banner_id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `image_url` varchar(255) NOT NULL,
  `link_url` varchar(255) DEFAULT NULL,
  `position` varchar(50) DEFAULT 'slider',
  `order_priority` int(11) DEFAULT 0,
  `status` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `banners`
--

INSERT INTO `banners` (`banner_id`, `title`, `image_url`, `link_url`, `position`, `order_priority`, `status`) VALUES
(1, 'Chương trình Khuyến mãi mùa hè cực cháy dành cho các BOSS', 'banner_1778675644_59470.png', '', 'slider', 0, 1),
(2, 'Thu Vàng Ưu Đãi – Mua Sắm Thả Ga Cho Boss!', 'banner_1778747961_55529.png', '', 'slider', 0, 1),
(3, 'Xuân Rộn Ràng – Sale Ngập Tràn Cho Boss Cưng!', 'banner_1778747972_74802.png', '', 'slider', 0, 1),
(4, 'Đông Ấm Áp – Sale Cực Đã Cho Boss Yêu!', 'banner_1778747985_14388.png', '', 'slider', 0, 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `brands`
--

CREATE TABLE `brands` (
  `brand_id` int(11) NOT NULL,
  `brand_name` varchar(100) NOT NULL,
  `brand_logo` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `brands`
--

INSERT INTO `brands` (`brand_id`, `brand_name`, `brand_logo`, `description`) VALUES
(1, 'Royal Canin', 'brand_1778674277_9464.png', 'Royal Canin là thương hiệu thức ăn thú cưng cao cấp đến từ Pháp, chuyên phát triển các công thức dinh dưỡng khoa học dành riêng cho từng giống chó mèo, độ tuổi và tình trạng sức khỏe, giúp hỗ trợ tối ưu cho sự phát triển và sức khỏe toàn diện của thú cưng.'),
(2, 'Pedigree', 'brand_1778674049_8407.png', 'Pedigree là thương hiệu thức ăn cho chó nổi tiếng toàn cầu, được phát triển dựa trên nghiên cứu dinh dưỡng của Mars Petcare và Waltham Petcare Science Institute. Thương hiệu tập trung vào việc cung cấp bữa ăn đầy đủ dưỡng chất, hỗ trợ tiêu hóa, da lông, răng miệng và hệ miễn dịch cho chó ở mọi độ tuổi và kích cỡ.'),
(3, 'Natural Core', 'brand_1778647652_7413.webp', 'Natural Core là thương hiệu tiên phong tại Hàn Quốc trong lĩnh vực thức ăn thú cưng hữu cơ. Triết lý của hãng là mang đến chế độ dinh dưỡng gần gũi với tự nhiên, sử dụng nguyên liệu được kiểm soát nghiêm ngặt, hạn chế chất bảo quản và các thành phần có thể gây hại cho sức khỏe thú cưng.'),
(4, 'Taste of the Wild', 'brand_1778674325_9906.jpg', 'Taste of the Wild là thương hiệu thức ăn thú cưng cao cấp đến từ Mỹ, nổi bật với công thức lấy cảm hứng từ chế độ ăn tự nhiên của động vật hoang dã, sử dụng các nguồn protein chất lượng như thịt bò rừng, cá hồi và nai, kết hợp cùng rau củ và probiotics để hỗ trợ tiêu hóa và sức khỏe toàn diện cho chó mèo.'),
(6, 'Bravecto', 'brand_1778751213_6236.png', 'Bravecto là thuốc thú y của MSD Animal Health, được sử dụng để phòng và điều trị ve, bọ chét cho chó mèo với hoạt chất fluralaner, có tác dụng kéo dài đến 12 tuần, giúp bảo vệ thú cưng hiệu quả và tiện lợi.'),
(7, 'KONG', 'brand_1778751300_4624.png', 'KONG'),
(8, 'PetSafe', 'brand_1778751342_4924.png', 'PetSafe là thương hiệu Mỹ chuyên cung cấp các sản phẩm chăm sóc và huấn luyện thú cưng như máy cho ăn tự động, đài nước uống, cửa thú cưng, hàng rào điện tử và vòng cổ huấn luyện, giúp nâng cao sự an toàn, tiện nghi và chất lượng cuộc sống cho chó mèo.'),
(9, 'Bio-Groom', 'brand_1778751386_7319.jpg', 'Bio-Groom là thương hiệu chăm sóc và làm đẹp thú cưng cao cấp đến từ Mỹ, nổi tiếng với các dòng dầu gội, dầu xả và sản phẩm vệ sinh được phát triển từ thành phần an toàn, dịu nhẹ, giúp làm sạch, dưỡng lông và chăm sóc da cho chó mèo một cách hiệu quả.'),
(10, 'Hill\'s Science Diet', 'brand_1778751419_7119.jpg', 'Hill\'s Science Diet là thương hiệu thức ăn thú cưng cao cấp của Hill\'s Pet Nutrition (Mỹ), được phát triển dựa trên nền tảng khoa học dinh dưỡng với các công thức cân bằng, giúp hỗ trợ sức khỏe toàn diện cho chó mèo theo từng độ tuổi, kích thước và nhu cầu cụ thể.'),
(11, 'TropiClean', 'brand_1778754076_5611.png', 'TropiClean là thương hiệu chăm sóc và vệ sinh thú cưng đến từ Mỹ, nổi tiếng với các sản phẩm dầu gội, xịt khử mùi, chăm sóc răng miệng và vệ sinh tai được chiết xuất từ thành phần tự nhiên, giúp làm sạch hiệu quả và mang lại hương thơm dễ chịu cho chó mèo.'),
(12, 'Virbac', 'brand_1778754121_5232.webp', 'Virbac là thương hiệu dược thú y hàng đầu đến từ Pháp, chuyên cung cấp các sản phẩm thuốc, dinh dưỡng bổ sung và giải pháp chăm sóc sức khỏe cho chó mèo, giúp hỗ trợ phòng ngừa và điều trị hiệu quả nhiều vấn đề về da, răng miệng, ký sinh trùng và sức khỏe tổng thể.'),
(13, 'Canada Pooch', 'brand_1778755382_4547.jpg', 'Canada Pooch là thương hiệu cao cấp đến từ Canada, chuyên thiết kế quần áo và phụ kiện thời trang cho chó như áo mưa, áo khoác mùa đông, giày và địu thú cưng, giúp thú cưng luôn thoải mái, ấm áp và phong cách trong mọi điều kiện thời tiết.');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(255) NOT NULL,
  `pet_type` enum('dog','cat','all') DEFAULT 'all',
  `parent_id` int(11) DEFAULT NULL,
  `status` tinyint(4) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `categories`
--

INSERT INTO `categories` (`category_id`, `category_name`, `pet_type`, `parent_id`, `status`) VALUES
(1, 'Thức Ăn Cho Chó', 'dog', NULL, 1),
(2, 'Bánh Thưởng', 'all', NULL, 1),
(3, 'Chăm Sóc Sức Khoẻ Cún', 'dog', NULL, 1),
(4, 'Chăm Sóc Vệ Sinh Cún', 'dog', NULL, 1),
(5, 'Phụ Kiện', 'all', NULL, 1),
(6, 'Đồ Chơi', 'all', NULL, 1),
(7, 'Vận Chuyển', 'all', NULL, 1),
(8, 'Thức Ăn Hạt', 'dog', 1, 1),
(9, 'Thức Ăn Ướt', 'dog', 1, 1),
(10, 'Thức Ăn Hỗ Trợ Điều Trị Bệnh', 'dog', 1, 1),
(11, 'Thức Ăn Hữu Cơ', 'dog', 1, 1),
(12, 'Thức Ăn Không Ngũ Cốc', 'dog', 1, 1),
(13, 'Bánh Thưởng Mềm', 'all', 2, 1),
(14, 'Xương Gặm Sạch Răng', 'dog', 2, 1),
(15, 'Súp Thưởng', 'dog', 2, 1),
(16, 'Bánh Quy', 'all', 2, 1),
(17, 'Thịt Sấy Khô', 'all', 2, 1),
(18, 'Vitamin Cho Chó', 'dog', 3, 1),
(19, 'Trị Ve Rận & Xổ Giun', 'dog', 3, 1),
(20, 'Thực Phẩm Chức Năng', 'dog', 3, 1),
(21, 'Vệ Sinh Răng Miệng', 'dog', 4, 1),
(22, 'Vệ Sinh Tai - Mắt', 'dog', 4, 1),
(23, 'Sữa Tắm & Phụ Kiện Tắm', 'dog', 4, 1),
(24, 'Xịt Khử Mùi', 'all', 4, 1),
(25, 'Vòng Cổ & Dây Dắt', 'all', 5, 1),
(26, 'Quần Áo & Nón Mũ', 'dog', 5, 1),
(27, 'Dụng Cụ Ăn Uống', 'dog', 5, 1),
(28, 'Nệm - Chuồng Cho Cún', 'dog', 5, 1),
(29, 'Tả Lót & Khay Vệ Sinh', 'dog', 5, 1),
(30, 'Xương Gặm', 'dog', 6, 1),
(31, 'Nhồi Bông', 'dog', 6, 1),
(32, 'Huấn Luyện & Tương Tác', 'dog', 6, 1),
(33, 'Balo & Túi Vận Chuyển', 'dog', 7, 1),
(34, 'Lồng Vận Chuyển', 'dog', 7, 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `coupons`
--

CREATE TABLE `coupons` (
  `coupon_id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `discount_type` enum('percentage','fixed') NOT NULL,
  `discount_value` decimal(10,2) NOT NULL,
  `min_order_value` decimal(10,2) DEFAULT 0.00,
  `max_discount` decimal(10,2) DEFAULT NULL,
  `usage_limit` int(11) DEFAULT NULL,
  `used_count` int(11) DEFAULT 0,
  `expiry_date` timestamp NULL DEFAULT NULL,
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `coupons`
--

INSERT INTO `coupons` (`coupon_id`, `code`, `discount_type`, `discount_value`, `min_order_value`, `max_discount`, `usage_limit`, `used_count`, `expiry_date`, `status`, `created_at`) VALUES
(1, 'PET10', 'percentage', 10.00, 0.00, 200000.00, 5, 0, '2026-05-20 16:59:59', 1, '2026-05-14 04:56:29'),
(2, 'SUMMERTHANG5', 'fixed', 20000.00, 100000.00, NULL, 100, 0, '2026-07-20 16:59:59', 1, '2026-05-14 04:57:39'),
(3, 'PETLOVE50', 'fixed', 50000.00, 200000.00, NULL, 10, 0, '2026-07-20 16:59:59', 1, '2026-05-14 04:58:09'),
(4, 'DOCHOI15', 'percentage', 15.00, 50000.00, 100000.00, 20, 0, '2026-07-20 16:59:59', 1, '2026-05-14 04:58:42'),
(5, 'KHACHMOI26', 'percentage', 10.00, 0.00, 10000000.00, 100, 0, '2026-07-20 16:59:59', 1, '2026-05-14 04:59:16');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `coupon_id` int(11) DEFAULT NULL,
  `total_price` decimal(10,2) DEFAULT NULL,
  `shipping_fee` decimal(10,2) DEFAULT 0.00,
  `discount_amount` decimal(10,2) DEFAULT 0.00,
  `order_status` enum('pending','confirmed','shipping','completed','cancelled') DEFAULT 'pending',
  `payment_method` varchar(50) DEFAULT NULL,
  `payment_status` enum('unpaid','paid','refunded') DEFAULT 'unpaid',
  `shipping_method` varchar(50) DEFAULT NULL,
  `shipping_address` text NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `orders`
--

INSERT INTO `orders` (`order_id`, `user_id`, `coupon_id`, `total_price`, `shipping_fee`, `discount_amount`, `order_status`, `payment_method`, `payment_status`, `shipping_method`, `shipping_address`, `notes`, `created_at`, `updated_at`) VALUES
(1, 2, NULL, 465000.00, 30000.00, 0.00, 'completed', 'cod', 'paid', 'standard', 'Lý Tự Trọng\r\nNinh Kiều, Phường An Khánh, Quận Ninh Kiều, Thành phố Cần Thơ', NULL, '2026-05-13 03:26:31', '2026-05-13 13:29:02');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `order_items`
--

CREATE TABLE `order_items` (
  `order_item_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price_at_purchase` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `order_items`
--

INSERT INTO `order_items` (`order_item_id`, `order_id`, `product_id`, `quantity`, `price_at_purchase`) VALUES
(1, 1, 1, 1, 135000.00),
(2, 1, 3, 1, 300000.00);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `order_logs`
--

CREATE TABLE `order_logs` (
  `log_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `old_status` varchar(50) DEFAULT NULL,
  `new_status` varchar(50) DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `changed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `order_logs`
--

INSERT INTO `order_logs` (`log_id`, `order_id`, `admin_id`, `old_status`, `new_status`, `reason`, `changed_at`) VALUES
(1, 1, 1, 'pending', 'confirmed', '', '2026-05-13 13:18:26'),
(2, 1, 1, 'confirmed', 'shipping', '', '2026-05-13 13:18:30'),
(3, 1, 1, 'shipping', 'completed', '', '2026-05-13 13:29:02');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `order_status_history`
--

CREATE TABLE `order_status_history` (
  `history_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `status` enum('pending','confirmed','shipping','completed','cancelled') NOT NULL,
  `changed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `order_status_history`
--

INSERT INTO `order_status_history` (`history_id`, `order_id`, `status`, `changed_at`) VALUES
(1, 1, 'confirmed', '2026-05-13 13:18:26'),
(2, 1, 'shipping', '2026-05-13 13:18:30'),
(3, 1, 'completed', '2026-05-13 13:29:02');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `pages`
--

CREATE TABLE `pages` (
  `page_id` int(11) NOT NULL,
  `page_title` varchar(255) NOT NULL,
  `page_slug` varchar(100) NOT NULL,
  `page_content` longtext NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `pages`
--

INSERT INTO `pages` (`page_id`, `page_title`, `page_slug`, `page_content`, `updated_at`) VALUES
(1, 'Tin Tức Shop Thú Cưng - Cẩm Nang Chăm Sóc, Dinh Dưỡng Và Kinh Nghiệm Nuôi Thú Cưng Toàn Diện', 'tin-tuc-shop-thu-cung-cam-nang-cham-soc-dinh-duong-va-kinh-nghiem-nuoi-thu-cung-toan-dien', 'Chào mừng bạn đến với chuyên mục Tin Tức Shop Thú Cưng – nơi cập nhật những kiến thức hữu ích, kinh nghiệm thực tế và xu hướng mới nhất trong việc chăm sóc chó, mèo, hamster, thỏ, chim cảnh và nhiều loài thú cưng khác. Tại đây, chúng tôi tổng hợp những bài viết chuyên sâu về dinh dưỡng, sức khỏe, huấn luyện, vệ sinh, làm đẹp và lựa chọn phụ kiện phù hợp nhằm giúp thú cưng của bạn luôn khỏe mạnh, vui vẻ và phát triển toàn diện.\n1. Tại Sao Việc Chăm Sóc Thú Cưng Đúng Cách Lại Quan Trọng?\nThú cưng không chỉ là vật nuôi mà còn là những người bạn thân thiết trong gia đình. Việc chăm sóc đúng cách giúp kéo dài tuổi thọ, tăng cường sức khỏe và cải thiện chất lượng cuộc sống của các bé. Một chế độ dinh dưỡng khoa học, môi trường sống sạch sẽ và sự quan tâm thường xuyên sẽ giúp thú cưng hạn chế bệnh tật, giảm stress và phát triển ổn định cả về thể chất lẫn tinh thần.\nNgoài ra, việc hiểu rõ nhu cầu từng giống loài còn giúp chủ nuôi đưa ra lựa chọn phù hợp về thức ăn, đồ chơi, chuồng trại và các sản phẩm hỗ trợ chăm sóc hằng ngày.\n2. Cập Nhật Tin Tức Mới Nhất Về Thị Trường Thú Cưng\nNgành công nghiệp thú cưng đang phát triển mạnh mẽ với hàng loạt sản phẩm và dịch vụ mới. Các thương hiệu nổi tiếng liên tục ra mắt thức ăn hữu cơ, hạt dinh dưỡng cao cấp, pate tươi, sữa bổ sung vitamin, thuốc hỗ trợ tiêu hóa, sản phẩm trị ve rận và nhiều phụ kiện thông minh.\nBên cạnh đó, xu hướng chăm sóc thú cưng hiện đại ngày càng chú trọng đến sức khỏe tinh thần, chế độ ăn cân bằng và các hoạt động vận động phù hợp. Chủ nuôi ngày nay không chỉ quan tâm đến việc cho ăn mà còn đầu tư vào dịch vụ spa, khách sạn thú cưng, huấn luyện hành vi và bảo hiểm sức khỏe cho thú cưng.\n3. Hướng Dẫn Chọn Thức Ăn Phù Hợp\nThức Ăn Cho Chó\nChó cần chế độ ăn giàu protein, chất béo tốt, vitamin và khoáng chất. Tùy theo độ tuổi và kích thước, bạn có thể lựa chọn hạt khô, pate, thức ăn tươi hoặc chế độ ăn tự nấu. Các giống chó nhỏ thường cần hạt kích thước nhỏ và dễ tiêu hóa, trong khi chó lớn cần bổ sung glucosamine để hỗ trợ xương khớp.\nThức Ăn Cho Mèo\nMèo là động vật ăn thịt bắt buộc, do đó cần lượng đạm động vật cao. Taurine là dưỡng chất quan trọng giúp bảo vệ tim mạch và thị lực. Ngoài thức ăn khô, bạn nên kết hợp pate hoặc súp thưởng để tăng lượng nước nạp vào cơ thể.\nThức Ăn Cho Hamster, Thỏ Và Chim Cảnh\nHamster cần hạt ngũ cốc, trái cây sấy và thức ăn chuyên dụng. Thỏ cần cỏ khô Timothy, rau xanh và viên nén giàu chất xơ. Chim cảnh cần hỗn hợp hạt, trái cây và vitamin để duy trì bộ lông đẹp và sức khỏe tốt.\n4. Những Bệnh Thường Gặp Ở Thú Cưng\nMột số vấn đề phổ biến bao gồm:\n\n\nRối loạn tiêu hóa do thay đổi thức ăn đột ngột.\n\n\nViêm da, nấm và ký sinh trùng ngoài da.\n\n\nBệnh đường hô hấp.\n\n\nBéo phì do ít vận động.\n\n\nCác bệnh về răng miệng.\n\n\nStress và rối loạn hành vi.\n\n\nViệc tiêm phòng đầy đủ, tẩy giun định kỳ và khám sức khỏe thường xuyên là yếu tố then chốt để phòng ngừa bệnh tật.\n5. Cách Chăm Sóc Lông Và Da\nBộ lông khỏe mạnh phản ánh tình trạng sức khỏe tổng thể của thú cưng. Chủ nuôi nên:\n\n\nChải lông hằng ngày.\n\n\nTắm bằng sữa tắm chuyên dụng.\n\n\nSử dụng dầu dưỡng và xịt khử mùi.\n\n\nBổ sung Omega 3 và Omega 6.\n\n\nKiểm tra ve rận thường xuyên.\n\n\nĐối với những giống chó mèo lông dài, việc cắt tỉa định kỳ giúp giảm rối lông và hạn chế các bệnh về da.\n6. Phụ Kiện Cần Thiết Cho Thú Cưng\nCác sản phẩm không thể thiếu gồm:\n\n\nBát ăn và bình nước tự động.\n\n\nNhà ngủ, ổ nằm mềm mại.\n\n\nChuồng vận chuyển.\n\n\nKhay vệ sinh và cát vệ sinh.\n\n\nVòng cổ, dây dắt.\n\n\nĐồ chơi tương tác.\n\n\nCây cào móng cho mèo.\n\n\nĐệm làm mát hoặc giữ ấm.\n\n\n7. Kinh Nghiệm Huấn Luyện Cơ Bản\nHuấn luyện thú cưng giúp hình thành thói quen tốt và tăng khả năng giao tiếp giữa chủ và vật nuôi. Các kỹ năng cơ bản gồm:\n\n\nĐi vệ sinh đúng chỗ.\n\n\nNgồi, nằm, đứng yên.\n\n\nKhông cắn phá đồ đạc.\n\n\nLàm quen với dây dắt.\n\n\nPhản hồi khi được gọi tên.\n\n\nPhương pháp thưởng bằng bánh snack và lời khen thường mang lại hiệu quả cao.\n8. Xu Hướng Nuôi Thú Cưng Hiện Đại\nNgày càng nhiều gia đình coi thú cưng như một thành viên chính thức. Điều này thúc đẩy nhu cầu về:\n\n\nThức ăn hữu cơ.\n\n\nDịch vụ spa và grooming.\n\n\nBảo hiểm thú cưng.\n\n\nThiết bị thông minh theo dõi sức khỏe.\n\n\nCamera quan sát thú cưng.\n\n\nĐồ chơi kích thích trí tuệ.\n\n\n9. Mẹo Giúp Thú Cưng Sống Khỏe Mạnh\n\n\nCung cấp nước sạch liên tục.\n\n\nDuy trì lịch tiêm phòng.\n\n\nCho vận động mỗi ngày.\n\n\nKiểm soát cân nặng.\n\n\nKhám sức khỏe định kỳ.\n\n\nGiữ môi trường sống sạch sẽ.\n\n\nDành thời gian chơi và tương tác.\n\n\n10. Những Sản Phẩm Được Yêu Thích Tại Shop Thú Cưng\nTại shop thú cưng, khách hàng có thể tìm thấy hàng nghìn sản phẩm chất lượng cao như:\n\n\nThức ăn hạt cho chó mèo mọi lứa tuổi.\n\n\nPate dinh dưỡng cao cấp.\n\n\nCát vệ sinh khử mùi.\n\n\nSữa tắm trị nấm và ve rận.\n\n\nVitamin tổng hợp.\n\n\nBánh thưởng huấn luyện.\n\n\nĐồ chơi gặm nhai.\n\n\nChuồng và balo vận chuyển.\n\n\n11. Tư Vấn Chọn Sản Phẩm Theo Độ Tuổi\nThú Cưng Sơ Sinh\nCần sữa thay thế, bình bú, đệm giữ nhiệt và sản phẩm hỗ trợ miễn dịch.\nThú Cưng Trưởng Thành\nCần chế độ ăn cân bằng, đồ chơi vận động và các sản phẩm chăm sóc lông.\nThú Cưng Cao Tuổi\nCần thực phẩm hỗ trợ khớp, tim mạch và tiêu hóa.\n12. Cộng Đồng Yêu Thú Cưng\nChuyên mục tin tức cũng là nơi kết nối cộng đồng những người yêu động vật. Bạn có thể tìm thấy các câu chuyện cảm động, kinh nghiệm nhận nuôi, chia sẻ về cứu hộ động vật và các hoạt động thiện nguyện dành cho chó mèo bị bỏ rơi.\n13. Cam Kết Từ Shop Thú Cưng\nChúng tôi luôn cập nhật những kiến thức mới nhất, lựa chọn sản phẩm chính hãng và mang đến dịch vụ tư vấn tận tâm. Mỗi bài viết trong chuyên mục tin tức đều được biên soạn nhằm giúp người nuôi hiểu rõ hơn về nhu cầu của thú cưng, từ đó chăm sóc các bé một cách khoa học và hiệu quả nhất.\n14. Kết Luận\nNuôi thú cưng là một hành trình đầy niềm vui và trách nhiệm. Với nguồn thông tin phong phú từ chuyên mục Tin Tức Shop Thú Cưng, bạn sẽ có thêm kiến thức để chăm sóc người bạn bốn chân của mình tốt hơn mỗi ngày. Hãy thường xuyên theo dõi chuyên mục để cập nhật những bài viết mới nhất về dinh dưỡng, sức khỏe, huấn luyện và các sản phẩm hữu ích dành cho thú cưng thân yêu của bạn.', '2026-05-13 12:46:57'),
(2, 'Cách Chọn Thức Ăn Phù Hợp Cho Chó Mèo Theo Độ Tuổi Và Nhu Cầu Dinh Dưỡng', 'cach-chon-thuc-an-phu-hop-cho-cho-meo-theo-do-tuoi-va-nhu-cau-dinh-duong', 'Việc lựa chọn thức ăn phù hợp đóng vai trò rất quan trọng đối với sức khỏe và sự phát triển toàn diện của thú cưng. Mỗi giai đoạn trong cuộc đời của chó mèo đều có nhu cầu dinh dưỡng khác nhau, vì vậy người nuôi cần hiểu rõ đặc điểm từng độ tuổi để lựa chọn sản phẩm phù hợp nhất.\n\nThức Ăn Cho Chó Mèo Con\n\nỞ giai đoạn từ 2 đến 12 tháng tuổi, chó mèo con cần lượng protein, chất béo, canxi và vitamin cao hơn để hỗ trợ sự phát triển của xương, cơ bắp và hệ miễn dịch. Những sản phẩm dành riêng cho puppy và kitten thường có kích thước hạt nhỏ, dễ nhai và dễ tiêu hóa.\n\nThức Ăn Cho Chó Mèo Trưởng Thành\n\nKhi bước sang giai đoạn trưởng thành, thú cưng cần chế độ ăn cân bằng để duy trì năng lượng và sức khỏe tổng thể. Chủ nuôi nên chọn sản phẩm dựa trên cân nặng, mức độ vận động và tình trạng sức khỏe của từng bé.\n\nThức Ăn Cho Chó Mèo Cao Tuổi\n\nThú cưng lớn tuổi thường gặp các vấn đề về xương khớp, tim mạch và tiêu hóa. Các loại thức ăn senior thường được bổ sung glucosamine, chondroitin, omega-3 và chất chống oxy hóa nhằm hỗ trợ quá trình lão hóa.\n\nCác Dòng Thức Ăn Được Ưa Chuộng\n\nMột số thương hiệu nổi tiếng được nhiều người tin dùng bao gồm Royal Canin, Hill\'s Science Diet, Purina Pro Plan, Taste of the Wild, Orijen, Acana và Farmina N&D. Mỗi thương hiệu đều có các công thức chuyên biệt cho từng giống loài và nhu cầu dinh dưỡng khác nhau.\n\nLưu Ý Khi Thay Đổi Thức Ăn\n\nKhi chuyển sang loại thức ăn mới, nên thực hiện từ từ trong 7–10 ngày để hệ tiêu hóa thích nghi tốt hơn. Đồng thời cần đảm bảo thú cưng luôn được cung cấp nước sạch đầy đủ.\n\nViệc đầu tư vào nguồn dinh dưỡng chất lượng là nền tảng giúp thú cưng khỏe mạnh, lông mượt, ít bệnh tật và sống lâu hơn.', '2026-05-14 09:45:34'),
(3, 'Những Phụ Kiện Không Thể Thiếu Khi Nuôi Chó Mèo Trong Nhà', 'nhung-phu-kien-khong-the-thieu-khi-nuoi-cho-meo-trong-nha', 'Bên cạnh thức ăn và chế độ chăm sóc, phụ kiện là yếu tố quan trọng giúp thú cưng có môi trường sống thoải mái và an toàn hơn. Việc chuẩn bị đầy đủ các vật dụng cần thiết cũng giúp chủ nuôi tiết kiệm thời gian và chăm sóc thú cưng hiệu quả hơn.\n\nBát Ăn Và Bình Nước\n\nBát ăn bằng inox hoặc gốm sứ dễ vệ sinh và an toàn cho thú cưng. Bình nước tự động giúp duy trì nguồn nước sạch liên tục trong ngày.\n\nỔ Nằm Và Nhà Ngủ\n\nMột chiếc đệm êm ái hoặc nhà ngủ kín đáo sẽ giúp chó mèo cảm thấy an toàn và ngủ ngon hơn, đặc biệt trong thời tiết lạnh.\n\nKhay Vệ Sinh Và Cát Cho Mèo\n\nĐối với mèo, khay vệ sinh và cát chất lượng tốt giúp hạn chế mùi hôi và giữ môi trường sống luôn sạch sẽ.\n\nVòng Cổ, Dây Dắt Và Thẻ Tên\n\nNhững phụ kiện này rất cần thiết khi đưa thú cưng ra ngoài, đồng thời giúp dễ dàng nhận diện khi thú cưng đi lạc.\n\nĐồ Chơi Tương Tác\n\nBóng, cần câu mèo, đồ chơi phát tiếng và đồ gặm giúp giảm stress, kích thích vận động và hạn chế hành vi phá phách.\n\nDụng Cụ Chăm Sóc\n\nLược chải lông, kềm cắt móng, bàn chải đánh răng và sữa tắm chuyên dụng là những vật dụng cần thiết để chăm sóc vệ sinh hằng ngày.\n\nChuồng Và Balo Vận Chuyển\n\nRất hữu ích khi đưa thú cưng đi khám bệnh, du lịch hoặc về quê. Các sản phẩm hiện đại còn được thiết kế thông thoáng và tiện lợi.\n\nCamera Và Thiết Bị Thông Minh\n\nNgày càng nhiều chủ nuôi lựa chọn camera quan sát và máy cho ăn tự động để theo dõi thú cưng ngay cả khi vắng nhà.\n\nChuẩn bị đầy đủ phụ kiện phù hợp không chỉ giúp thú cưng thoải mái hơn mà còn góp phần xây dựng một môi trường sống khoa học, sạch sẽ và an toàn cho cả gia đình.', '2026-05-14 09:45:58');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `posts`
--

CREATE TABLE `posts` (
  `post_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `content` longtext NOT NULL,
  `thumbnail` varchar(255) DEFAULT NULL,
  `category` enum('blog','news') DEFAULT 'blog',
  `author_id` int(11) NOT NULL,
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `posts`
--

INSERT INTO `posts` (`post_id`, `title`, `slug`, `content`, `thumbnail`, `category`, `author_id`, `status`, `created_at`) VALUES
(1, 'Ưu đãi đạm sâu cho các khách hàng mới trong năm 2026', 'KM01', '🎉 Ưu Đãi Đặc Biệt Cho Khách Hàng Mới! 🎉\n\nChào mừng bạn đến với cửa hàng! Nhập mã KHACHMOI26 để nhận ngay ưu đãi hấp dẫn cho đơn hàng đầu tiên và trải nghiệm hàng ngàn sản phẩm chất lượng dành cho thú cưng.\n\n🐾 Thức ăn dinh dưỡng cao cấp\n🐾 Phụ kiện, đồ chơi và sản phẩm chăm sóc thú cưng\n🐾 Chính hãng 100%, giá tốt mỗi ngày\n\n✨ Mã giảm giá: KHACHMOI26\n🎁 Áp dụng cho: Khách hàng mới\n🛒 Sử dụng ngay khi thanh toán\n\nNhanh tay đặt hàng hôm nay để nhận ưu đãi chào mừng và mang đến cho thú cưng của bạn những sản phẩm tốt nhất! 🐶🐱💖', 'post_1778751817_9953.png', 'blog', 1, 1, '2026-05-13 12:06:28'),
(2, 'Ưu Đãi Đồ Chơi Cho Thú Cưng – Vui Chơi Thỏa Thích!', 'KM02', '🧸 Ưu Đãi Đồ Chơi Cho Thú Cưng – Vui Chơi Thỏa Thích! 🐾\n\nBiến mỗi ngày của boss thêm thú vị với những món đồ chơi hấp dẫn! Nhập mã DOCHOI15 để nhận ngay ưu đãi đặc biệt khi mua các sản phẩm đồ chơi cho chó mèo.\n\n🎾 Bóng, dây thừng, gặm nhai và đồ chơi tương tác\n🐶 Giúp vận động, giảm căng thẳng và hạn chế cắn phá\n🐱 Kích thích bản năng săn mồi và tăng sự năng động\n\n✨ Mã giảm giá: DOCHOI15\n🎁 Áp dụng cho: Tất cả sản phẩm đồ chơi thú cưng\n🛒 Nhập mã khi thanh toán để nhận ưu đãi\n\nNhanh tay chọn ngay những món đồ chơi yêu thích để thú cưng luôn vui khỏe và tràn đầy năng lượng! 🐕🐈💖', 'post_1778752102_6286.png', 'blog', 1, 1, '2026-05-14 09:48:22'),
(3, 'Mã Ưu Đãi Hấp Dẫn Dành Cho Sen Yêu Boss!', 'KM03', '🎉 Mã Ưu Đãi Hấp Dẫn Dành Cho Sen Yêu Boss! 🐾\n\n💖 PETLOVE50 – Gửi Trọn Yêu Thương Cho Thú Cưng\n\nNhập mã PETLOVE50 để nhận ưu đãi đặc biệt, giúp bạn tiết kiệm hơn khi chọn mua thức ăn, phụ kiện và sản phẩm chăm sóc chất lượng cho boss yêu. 🐶🐱💕\n\n☀️ SUMMERTHANG5 – Ưu Đãi Mùa Hè Sảng Khoái\n\nMùa hè đến rồi! Sử dụng mã SUMMERTHANG5 để nhận ngay khuyến mãi hấp dẫn cho các sản phẩm thiết yếu, giúp thú cưng luôn khỏe mạnh và năng động trong những ngày nắng nóng. 🌴🐾\n\n🎁 PET10 – Giảm Giá Mỗi Ngày\n\nNhập mã PET10 để nhận ưu đãi tiện lợi cho nhiều sản phẩm dành cho chó mèo, từ thức ăn dinh dưỡng đến phụ kiện và đồ chơi yêu thích. 🛍️🐕🐈\n\n✨ Cách sử dụng: Nhập mã khuyến mãi tương ứng tại bước thanh toán để được áp dụng ưu đãi.\n\n🛒 Mua sắm ngay hôm nay để chăm sóc thú cưng với những sản phẩm chất lượng cùng mức giá tiết kiệm nhất! 💖🐾', 'post_1778752324_4471.png', 'blog', 1, 1, '2026-05-14 09:52:04');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `brand_id` int(11) DEFAULT NULL,
  `promotion_id` int(11) DEFAULT NULL,
  `product_name` varchar(255) NOT NULL,
  `sku` varchar(50) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `discount_price` decimal(10,2) DEFAULT 0.00,
  `thumbnail` varchar(255) DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `stock_quantity` int(11) DEFAULT 0,
  `status` enum('active','inactive','out_of_stock') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `products`
--

INSERT INTO `products` (`product_id`, `category_id`, `brand_id`, `promotion_id`, `product_name`, `sku`, `price`, `discount_price`, `thumbnail`, `description`, `stock_quantity`, `status`, `created_at`, `updated_at`) VALUES
(1, 8, 2, NULL, 'Thức ăn hạt Pedigree vị bò', 'DOG-FOOD-001', 150000.00, 135000.00, 'product_1778647264_80451.jpg', 'Thức ăn hạt giàu dinh dưỡng cho chó trưởng thành.', 44, 'active', '2026-04-27 06:35:34', '2026-05-13 04:57:10'),
(2, 9, 1, NULL, 'Pate lon Royal Canin Puppy', 'DOG-FOOD-002', 45000.00, 0.00, 'product_1778677749_31818.jpg', 'Pate thơm ngon giúp kích thích vị giác cho cún con.', 100, 'active', '2026-04-27 06:35:34', '2026-05-13 13:09:09'),
(3, 10, NULL, NULL, 'Hạt hỗ trợ tiêu hóa cho chó nhạy cảm', 'DOG-FOOD-003', 320000.00, 300000.00, 'product_1778647490_76895.webp', 'Công thức đặc biệt dành cho chó có hệ tiêu hóa yếu.', 18, 'active', '2026-04-27 06:35:34', '2026-05-13 04:44:50'),
(4, 11, 3, NULL, 'Thức ăn hữu cơ Natural Core', 'DOG-FOOD-004', 450000.00, 0.00, 'product_1778677250_99955.jpg', 'Thành phần 100% tự nhiên, không chất bảo quản.', 15, 'active', '2026-04-27 06:35:34', '2026-05-13 13:00:56'),
(5, 12, 4, NULL, 'Hạt không ngũ cốc Taste of the Wild', 'DOG-FOOD-005', 550000.00, 520000.00, 'product_1778676915_37020.png', 'Phù hợp cho chó dị ứng với các loại ngũ cốc.', 20, 'active', '2026-04-27 06:35:34', '2026-05-14 08:21:07'),
(6, 13, NULL, NULL, 'Bánh thưởng mềm vị gà Bowwow', 'TREAT-001', 35000.00, 0.00, 'product_1778677786_83592.webp', 'Bánh thưởng mềm, thơm mùi gà, thích hợp để huấn luyện.', 200, 'active', '2026-04-27 06:35:34', '2026-05-13 13:09:46'),
(7, 14, NULL, NULL, 'Xương gặm sạch răng Pedigree Dentastix', 'TREAT-002', 25000.00, 0.00, 'product_1778677817_25262.png', 'Giúp giảm mảng bám và vôi răng cho cún.', 150, 'active', '2026-04-27 06:35:34', '2026-05-13 13:10:17'),
(8, 15, NULL, NULL, 'Súp thưởng Wanpy cho chó', 'TREAT-003', 12000.00, 0.00, 'product_1778677843_97112.jpg', 'Súp dạng lỏng, bổ sung nước và dưỡng chất.', 300, 'active', '2026-04-27 06:35:34', '2026-05-13 13:10:43'),
(9, 16, NULL, NULL, 'Bánh quy hình xương Milk Biscuit', 'TREAT-004', 65000.00, 0.00, 'product_1778677715_48899.webp', 'Bánh quy giòn tan, giàu canxi.', 79, 'active', '2026-04-27 06:35:34', '2026-05-13 13:08:35'),
(10, 17, NULL, NULL, 'Thịt bò sấy khô nguyên miếng', 'TREAT-005', 120000.00, 0.00, 'product_1778677898_26587.png', 'Thịt bò thật sấy lạnh, giữ nguyên hương vị.', 40, 'active', '2026-04-27 06:35:34', '2026-05-13 13:11:38'),
(11, 25, NULL, NULL, 'Vòng cổ vải dù phản quang', 'ACC-001', 50000.00, 0.00, 'product_1778751553_37224.jpg', 'Dây dù bền chắc, có phản quang an toàn khi đi đêm.', 60, 'active', '2026-04-27 06:35:34', '2026-05-14 09:39:13'),
(13, 20, 10, NULL, 'Thức ăn khô cho chó trưởng thành', 'DOG-FOOD-006', 120000.00, 0.00, 'product_1778753090_99760.jpg', 'Thức ăn khô cho chó trưởng thành Hill\'s Science Diet Adult Perfect Digestion hương vị gà, gạo lứt và yến mạch nguyên hạt là thức ăn khô được chế biến dành cho chó trưởng thành thuộc mọi giống và kích cỡ. Sản phẩm được làm từ thịt gà chất lượng cao là thành phần chính số 1, và chứa hỗn hợp prebiotic độc quyền có tên ActivBiome+ giúp hỗ trợ sức khỏe tiêu hóa của chó.\n\nCác tính năng chính:\nThúc đẩy sự đều đặn và phân khỏe mạnh\nHỗ trợ sức khỏe tiêu hóa tối ưu và hệ vi sinh vật đường ruột khỏe mạnh\nĐược làm từ thịt gà chất lượng cao là thành phần chính số 1\nChứa ActivBiome, một hỗn hợp prebiotic độc quyền\nChứa chất chống oxy hóa đã được chứng minh lâm sàng giúp tăng cường hệ miễn dịch\nKhông chứa hương liệu, màu sắc hoặc chất bảo quản nhân tạo\nNhìn chung, thức ăn khô cho chó trưởng thành Hill\'s Science Diet Adult Perfect Digestion hương vị gà, gạo lứt và yến mạch nguyên hạt là một lựa chọn tốt cho chó trưởng thành thuộc mọi giống và kích cỡ. Sản phẩm được làm từ các thành phần chất lượng cao và được chế biến để hỗ trợ sức khỏe tiêu hóa của chó. Nếu bạn đang tìm kiếm loại thức ăn khô cho chó giúp chó cưng của bạn luôn khỏe mạnh và có hệ tiêu hóa tốt, thì Hill\'s Science Diet Adult Perfect Digestion Chicken, Brown Rice, & Whole Oats Recipe Dry Dog Food là một lựa chọn tốt.', 100, 'active', '2026-05-14 10:04:50', '2026-05-14 10:04:50'),
(14, 19, 6, NULL, 'Thuốc trị ve rạn', 'DOG-FOOD-007', 120000.00, 0.00, 'product_1778753309_72027.jpg', 'Điều cần biết\nViên nhai Bravecto (thuốc trị ve rận cho chó 5–10 kg) được ưa chuộng nhờ hiệu quả kéo dài đến 12 tuần chỉ với một liều, giúp chủ nuôi tiết kiệm thời gian và giảm rủi ro bỏ sót liều. Sản phẩm được nhiều bác sĩ thú y khuyên dùng, dễ cho ăn và không gây bẩn như thuốc nhỏ lưng, mang lại sự an tâm cho người nuôi thú cưng.', 100, 'active', '2026-05-14 10:08:29', '2026-05-14 10:08:29'),
(15, 18, 1, NULL, 'THỨC ĂN CHO CHÓ ROYAL CANIN MINI ADULT ', 'DOG-FOOD-008', 140000.00, 0.00, 'product_1778753692_20765.jpg', 'Duy trì trọng lượng lý tưởng\n\nThức ăn cho chó ROYAL CANIN Mini Adult giúp duy trì cân nặng lý tưởng của các giống chó nhỏ bằng cách đáp ứng nhu cầu năng lượng cao của chúng và kích thích quá trình chuyển hóa chất béo bằng công thức L-carnitine để thúc đẩy sự trao đổi chất béo.\n\nTăng cường tính ngon miệng\n\nCông thức đặc biệt và hương vị độc đáo của ROYAL CANIN Mini Adult giúp đáp ứng nhu cầu ăn uống của giống chó nhỏ.\n\nTăng độ óng mượt cho lông và khỏe mạnh làn da\n\nChứa hàm lượng EPA & DHA lý tưởng để hỗ trợ lớp lông óng mượt và làn da khỏe mạnh.\n\nSức khỏe răng miệng\n\nGiảm thiểu quá trình hình thành cao răng nhờ tác động thải loại canxi.\n\nTHÀNH PHẦN\n\nNguyên liệu\n\nProtein gia cầm, bắp, bột bắp, chất béo động vật, gluten bắp, protein thực vật*, lúa mì, protein động vật, gạo, củ cải đường, khoáng chất, dầu cá, dầu đậu nành, men, fructo-oligo-sacarit.\n\nPhụ gia dinh dưỡng: Vitamin A, Vitamin D3, E1 (Sắt), E2 (I ốt), E4 (Đồng), E5 (Mangan), E6 (Kẽm), E8 (Selen), L-Carnitine - Chất chống oxi hóa.\n\n* L.I.P.: Protein có độ tiêu hóa cao.', 20, 'active', '2026-05-14 10:14:52', '2026-05-14 10:14:52'),
(16, 23, 9, NULL, 'Bio-Groom Herbal Groom Dog Shampoo', 'DOG-FOOD-009', 5000000.00, 0.00, 'product_1778753804_79328.jpg', 'Bio-Groom Herbal Groom là dầu gội thảo dược dịu nhẹ giúp làm sạch mà không gây cay mắt, rất phù hợp cho chó con hoặc thú cưng có da nhạy cảm. Công thức tự nhiên với chiết xuất thực vật giúp lông mềm mượt, giảm rối và duy trì độ bóng khỏe tự nhiên. Người dùng đánh giá cao mùi hương dễ chịu và khả năng làm sạch nhẹ nhàng mà vẫn hiệu quả.', 20, 'active', '2026-05-14 10:16:44', '2026-05-14 10:16:44'),
(17, 21, 12, NULL, 'Virbac C.E.T. Enzymatic Toothpaste', 'DOG-FOOD-0010', 321000.00, 0.00, 'product_1778753902_66481.jpg', 'Kem đánh răng enzyme Virbac C.E.T. giúp duy trì sức khỏe răng miệng cho chó và mèo nhờ công thức enzyme đặc biệt hỗ trợ kiểm soát mảng bám và hơi thở hôi. Sản phẩm được nhiều chuyên gia và người nuôi thú cưng tin dùng, đồng thời được The Spruce Pets xếp hạng là kem đánh răng thú cưng tốt nhất tổng thể.', 100, 'active', '2026-05-14 10:18:22', '2026-05-14 10:22:19'),
(18, 22, 11, NULL, 'TropiClean Ear Cleaning Wipes', 'DOG-FOOD-0011', 648000.00, 0.00, 'product_1778754036_45666.jpg', 'Khăn lau tai TropiClean (TropiClean Ear Cleaning Wipes) là lựa chọn tuyệt vời để giữ vệ sinh tai cho thú cưng nhờ công thức dịu nhẹ có nguồn gốc tự nhiên, giúp loại bỏ bụi bẩn và mùi hôi mà không gây kích ứng. Sản phẩm đặc biệt phù hợp với chó và mèo có làn da nhạy cảm, đồng thời mang lại cảm giác tươi mát dễ chịu sau khi sử dụng.', 100, 'active', '2026-05-14 10:20:36', '2026-05-14 10:22:15'),
(19, 24, 9, NULL, 'Bio-Groom So-Stinky Scented Odor Remover Pet Spray', 'DOG-FOOD-0012', 2000000.00, 0.00, 'product_1778754235_24875.jpg', 'Bio-Groom So-Stinky Scented Odor Remover Pet Spray là lựa chọn lý tưởng cho người nuôi thú cưng muốn loại bỏ mùi hôi trên lông và da thú mà không cần tắm thường xuyên. Sản phẩm nổi bật nhờ khả năng khử mùi hiệu quả, an toàn cho thú cưng và để lại hương thơm nhẹ dễ chịu, giúp vật nuôi luôn sạch sẽ và thơm mát.', 130, 'active', '2026-05-14 10:23:55', '2026-05-14 10:23:55'),
(20, 32, 7, NULL, 'KONG Classic Dog Toy', 'DOG-FOOD-0013', 20000.00, 0.00, 'product_1778754510_70958.jpg', 'KONG Classic Dog Toy (Đồ chơi chó KONG Classic) là lựa chọn lý tưởng cho những chú chó thích nhai, giúp giảm buồn chán và lo âu bằng cách kích thích trí não và bản năng săn mồi. Sản phẩm được làm từ cao su tự nhiên bền chắc, an toàn, và có thể nhồi thức ăn để tăng sự tương tác, khiến nó trở thành “tiêu chuẩn vàng” trong đồ chơi cho chó.', 1000, 'active', '2026-05-14 10:28:30', '2026-05-14 10:28:30'),
(21, 31, 7, NULL, 'Kong Cozie Dog Toy Elmer Elephant', 'DOG-FOOD-0014', 400000.00, 0.00, 'product_1778754596_30878.jpg', 'KONG Cozie Elmer the Elephant là món đồ chơi mềm được nhiều người nuôi chó chọn cho các bé cưng thích ôm ấp và chơi nhẹ nhàng. Thiết kế bằng vải plush mềm mại, có thêm lớp lót tăng độ bền, phù hợp cho các buổi chơi trong nhà và giúp chó con hoặc chó trưởng thành cảm thấy thoải mái, thư giãn.\n', 100, 'active', '2026-05-14 10:29:56', '2026-05-14 10:31:13'),
(22, 30, 7, NULL, 'Đồ chơi xương KONG Goodie Bone size M', 'DOG-FOOD-0015', 543000.00, 0.00, 'product_1778754665_47809.jpg', 'Đồ chơi xương KONG Goodie Bone size M rất được ưa chuộng vì độ bền cao và khả năng giúp chó cưng giải tỏa năng lượng nhai cắn. Thiết kế từ cao su tự nhiên chắc chắn, có thể nhét bánh thưởng, giúp giữ chó hứng thú và chơi lâu hơn. Đây là lựa chọn lý tưởng cho các bé cưng có thói quen gặm mạnh, giúp bảo vệ đồ đạc trong nhà.', 100, 'active', '2026-05-14 10:31:05', '2026-05-14 10:31:05'),
(23, 27, 8, NULL, 'Healthy Pet Water Station PetSafe', 'DOG-FOOD-0016', 1000000.00, 0.00, 'product_1778754790_88690.jpg', 'PetSafe Healthy Pet Water Station (Bình nước PetSafe Healthy Pet Water Station) là lựa chọn tiện lợi giúp thú cưng luôn có nguồn nước sạch, tươi mà không cần điện. Thiết kế bằng thép không gỉ dễ vệ sinh và vận hành theo cơ chế trọng lực giúp nước luôn đầy vừa phải, phù hợp cho cả chó và mèo ở nhiều kích cỡ.', 150, 'active', '2026-05-14 10:33:10', '2026-05-14 10:33:10'),
(24, 28, 8, NULL, 'Ghế cho cúng ', 'DOG-FOOD-0017', 670000.00, 0.00, 'product_1778755025_40910.jpg', 'PetSafe cung cấp các dòng ghế và đệm ngồi cho thú cưng được thiết kế êm ái, chắc chắn và dễ vệ sinh, giúp chó mèo có không gian nghỉ ngơi thoải mái, an toàn và phù hợp khi ở trong nhà hoặc di chuyển trên ô tô.', 50, 'active', '2026-05-14 10:37:05', '2026-05-14 10:37:05'),
(25, 33, 8, NULL, 'Balo vận chuyển pet ', 'DOG-FOOD-0018', 890000.00, 0.00, 'product_1778755137_62392.webp', 'PetSafe Happy Ride Backpack Pet Carrier là ba lô vận chuyển thú cưng cao cấp của PetSafe, được thiết kế dành cho chó và mèo dưới 9 kg (20 lb), với khung chắc chắn, cửa lưới thoáng khí, đệm êm ái và dây cố định an toàn, giúp thú cưng thoải mái và an toàn trong các chuyến đi, dạo phố hoặc du lịch.', 20, 'active', '2026-05-14 10:38:57', '2026-05-14 10:38:57'),
(26, 34, 8, NULL, 'PetSafe Portable Pet Kennel', 'DOG-FOOD-0019', 670000.00, 0.00, 'product_1778755205_72626.jpg', 'PetSafe Portable Pet Kennel là chuồng vận chuyển thú cưng di động được thiết kế gọn nhẹ, chắc chắn và dễ gấp gọn, giúp chó mèo có không gian an toàn, thoải mái khi đi du lịch, dã ngoại hoặc di chuyển bằng ô tô. Với các mặt lưới thoáng khí, đệm êm ái và cấu trúc dễ vệ sinh, sản phẩm mang đến sự tiện lợi tối đa cho cả thú cưng và người nuôi.', 30, 'active', '2026-05-14 10:40:05', '2026-05-14 10:40:05'),
(27, 26, 13, NULL, 'Waterproof Puffer', 'DOG-FOOD-0020', 600000.00, 0.00, 'product_1778755333_53724.webp', 'Giữ ấm và khô ráo cho chú chó của bạn với chiếc áo khoác mùa đông dày dặn, kinh điển này. Không chỉ trông đẹp mắt, nó còn có lớp vỏ chống thấm nước giúp giữ cho bộ lông của chúng luôn sạch sẽ khỏi mưa và tuyết.\n\nCÁCH HOẠT ĐỘNG\n\n- Lớp vỏ chống thấm nước với vải chần bông được ép nhiệt ngăn nước thấm vào\n\n- Lớp cách nhiệt giả lông vũ giúp giữ ấm cho chú chó của bạn\n\nNHỮNG TÍNH NĂNG BẠN SẼ YÊU THÍCH\n\n- Kiểu dáng thanh lịch với khóa dán đôi và dây rút điều chỉnh ở viền áo\n\n- Lớp lót dệt không rụng lông\n- Khóa kéo ở phía sau giúp dễ dàng gắn dây xích (rất phù hợp với dây đeo của chúng tôi!)', 100, 'active', '2026-05-14 10:42:13', '2026-05-14 10:43:10'),
(28, 29, 8, NULL, 'PetSafe ScoopFree', 'DOG-FOOD-0021', 500000.00, 0.00, 'product_1778755470_67907.jpg', 'PetSafe ScoopFree là dòng khay vệ sinh mèo tự động của PetSafe, được thiết kế để tự động dọn chất thải sau mỗi lần mèo sử dụng, giúp kiểm soát mùi hiệu quả, giữ khay luôn sạch sẽ và tiết kiệm thời gian chăm sóc cho người nuôi.', 50, 'active', '2026-05-14 10:44:30', '2026-05-14 10:44:30');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `product_images`
--

CREATE TABLE `product_images` (
  `image_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `image_url` varchar(255) NOT NULL,
  `is_main` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `product_stock_logs`
--

CREATE TABLE `product_stock_logs` (
  `log_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `type` enum('import','export') NOT NULL,
  `quantity` int(11) NOT NULL,
  `current_stock` int(11) NOT NULL,
  `new_stock` int(11) NOT NULL,
  `note` text DEFAULT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `product_stock_logs`
--

INSERT INTO `product_stock_logs` (`log_id`, `product_id`, `type`, `quantity`, `current_stock`, `new_stock`, `note`, `admin_id`, `created_at`) VALUES
(1, 5, 'import', 11, 9, 20, 'nhập kho', 1, '2026-05-14 08:21:07');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `promotions`
--

CREATE TABLE `promotions` (
  `promotion_id` int(11) NOT NULL,
  `promotion_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `discount_percent` int(11) NOT NULL,
  `start_date` timestamp NULL DEFAULT NULL,
  `end_date` timestamp NULL DEFAULT NULL,
  `status` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `return_requests`
--

CREATE TABLE `return_requests` (
  `return_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `reason` text NOT NULL,
  `request_type` enum('return','exchange') NOT NULL,
  `status` enum('pending','approved','rejected','completed') DEFAULT 'pending',
  `admin_note` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `reviews`
--

CREATE TABLE `reviews` (
  `review_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `rating` tinyint(4) DEFAULT NULL CHECK (`rating` between 1 and 5),
  `comment` text DEFAULT NULL,
  `status` tinyint(4) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `review_reports`
--

CREATE TABLE `review_reports` (
  `report_id` int(11) NOT NULL,
  `review_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `reason` text NOT NULL,
  `status` tinyint(4) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `shipping_zones`
--

CREATE TABLE `shipping_zones` (
  `zone_id` int(11) NOT NULL,
  `zone_name` varchar(100) NOT NULL,
  `shipping_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `estimated_delivery` varchar(50) DEFAULT NULL,
  `status` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `fullname` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `avatar` varchar(255) DEFAULT 'default-avatar.png',
  `role` enum('admin','customer') DEFAULT 'customer',
  `status` tinyint(1) DEFAULT 1,
  `lock_reason` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`user_id`, `username`, `email`, `password`, `fullname`, `phone`, `address`, `avatar`, `role`, `status`, `lock_reason`, `created_at`) VALUES
(1, 'admin', 'admin@gmail.com', '$2y$10$LRGZ2PKkqCbkUcXDnKcL9OmVpHBfUEsHewzzNhT4yefC8QOnHwAB.', 'admin', '0987123456', 'Cái Răng, Cần Thơ', 'default-avatar.png', 'admin', 1, NULL, '2026-04-28 08:28:45'),
(2, 'user', 'user@gmail.com', '$2y$10$wI8Zjf14MZi/J2oPumHit.0j2w67xHK4cBC.t3BzKGrcd9ohdrfdK', 'Trần User', '0987654731', 'Lý Tự Trọng\r\nNinh Kiều, Phường An Khánh, Quận Ninh Kiều, Thành phố Cần Thơ', 'default-avatar.png', 'customer', 1, NULL, '2026-04-30 13:24:17'),
(3, 'user123', 'user123@gmail.com', '$2y$10$l.dYPHEhYzSZ0vqqqSpGkeLuIBgcqQPvFCyL/n0xqhwIGbL48TJH2', 'Trần Huy Ngô', '0987654752', '123 Trần Chiên, quận Cái Răng, thành phố Cần Thơ', 'default-avatar.png', 'customer', 1, NULL, '2026-05-14 09:54:34');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `user_addresses`
--

CREATE TABLE `user_addresses` (
  `address_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `receiver_name` varchar(255) DEFAULT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `address_detail` text DEFAULT NULL,
  `is_default` tinyint(4) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `wishlists`
--

CREATE TABLE `wishlists` (
  `wishlist_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `wishlists`
--

INSERT INTO `wishlists` (`wishlist_id`, `user_id`, `product_id`, `created_at`) VALUES
(2, 2, 1, '2026-05-12 09:43:09');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `banners`
--
ALTER TABLE `banners`
  ADD PRIMARY KEY (`banner_id`);

--
-- Chỉ mục cho bảng `brands`
--
ALTER TABLE `brands`
  ADD PRIMARY KEY (`brand_id`);

--
-- Chỉ mục cho bảng `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`),
  ADD KEY `fk_parent` (`parent_id`);

--
-- Chỉ mục cho bảng `coupons`
--
ALTER TABLE `coupons`
  ADD PRIMARY KEY (`coupon_id`),
  ADD UNIQUE KEY `unique_code` (`code`);

--
-- Chỉ mục cho bảng `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `fk_order_user` (`user_id`),
  ADD KEY `idx_status` (`order_status`),
  ADD KEY `idx_created` (`created_at`),
  ADD KEY `fk_order_coupon` (`coupon_id`);

--
-- Chỉ mục cho bảng `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`order_item_id`),
  ADD KEY `fk_items_order` (`order_id`),
  ADD KEY `fk_items_product` (`product_id`);

--
-- Chỉ mục cho bảng `order_logs`
--
ALTER TABLE `order_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `fk_log_order` (`order_id`);

--
-- Chỉ mục cho bảng `order_status_history`
--
ALTER TABLE `order_status_history`
  ADD PRIMARY KEY (`history_id`),
  ADD KEY `fk_history_order` (`order_id`);

--
-- Chỉ mục cho bảng `pages`
--
ALTER TABLE `pages`
  ADD PRIMARY KEY (`page_id`);

--
-- Chỉ mục cho bảng `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`post_id`),
  ADD KEY `fk_post_author` (`author_id`);

--
-- Chỉ mục cho bảng `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`),
  ADD UNIQUE KEY `sku` (`sku`),
  ADD KEY `fk_product_category` (`category_id`),
  ADD KEY `fk_product_brand` (`brand_id`),
  ADD KEY `fk_product_promotion` (`promotion_id`);

--
-- Chỉ mục cho bảng `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`image_id`),
  ADD KEY `fk_images_product` (`product_id`);

--
-- Chỉ mục cho bảng `product_stock_logs`
--
ALTER TABLE `product_stock_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Chỉ mục cho bảng `promotions`
--
ALTER TABLE `promotions`
  ADD PRIMARY KEY (`promotion_id`);

--
-- Chỉ mục cho bảng `return_requests`
--
ALTER TABLE `return_requests`
  ADD PRIMARY KEY (`return_id`),
  ADD KEY `fk_return_order` (`order_id`),
  ADD KEY `fk_return_user` (`user_id`);

--
-- Chỉ mục cho bảng `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD KEY `fk_review_product` (`product_id`),
  ADD KEY `fk_review_user` (`user_id`),
  ADD KEY `fk_review_order` (`order_id`);

--
-- Chỉ mục cho bảng `review_reports`
--
ALTER TABLE `review_reports`
  ADD PRIMARY KEY (`report_id`),
  ADD KEY `fk_report_review` (`review_id`),
  ADD KEY `fk_report_user` (`user_id`);

--
-- Chỉ mục cho bảng `shipping_zones`
--
ALTER TABLE `shipping_zones`
  ADD PRIMARY KEY (`zone_id`);

--
-- Chỉ mục cho bảng `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Chỉ mục cho bảng `user_addresses`
--
ALTER TABLE `user_addresses`
  ADD PRIMARY KEY (`address_id`),
  ADD KEY `fk_address_user` (`user_id`);

--
-- Chỉ mục cho bảng `wishlists`
--
ALTER TABLE `wishlists`
  ADD PRIMARY KEY (`wishlist_id`),
  ADD UNIQUE KEY `unique_user_product` (`user_id`,`product_id`),
  ADD KEY `fk_wishlist_product` (`product_id`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `banners`
--
ALTER TABLE `banners`
  MODIFY `banner_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT cho bảng `brands`
--
ALTER TABLE `brands`
  MODIFY `brand_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT cho bảng `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT cho bảng `coupons`
--
ALTER TABLE `coupons`
  MODIFY `coupon_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT cho bảng `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT cho bảng `order_items`
--
ALTER TABLE `order_items`
  MODIFY `order_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT cho bảng `order_logs`
--
ALTER TABLE `order_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `order_status_history`
--
ALTER TABLE `order_status_history`
  MODIFY `history_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `pages`
--
ALTER TABLE `pages`
  MODIFY `page_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `posts`
--
ALTER TABLE `posts`
  MODIFY `post_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT cho bảng `product_images`
--
ALTER TABLE `product_images`
  MODIFY `image_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `product_stock_logs`
--
ALTER TABLE `product_stock_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT cho bảng `promotions`
--
ALTER TABLE `promotions`
  MODIFY `promotion_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `return_requests`
--
ALTER TABLE `return_requests`
  MODIFY `return_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `reviews`
--
ALTER TABLE `reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `review_reports`
--
ALTER TABLE `review_reports`
  MODIFY `report_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `shipping_zones`
--
ALTER TABLE `shipping_zones`
  MODIFY `zone_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `user_addresses`
--
ALTER TABLE `user_addresses`
  MODIFY `address_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `wishlists`
--
ALTER TABLE `wishlists`
  MODIFY `wishlist_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `fk_parent` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`category_id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_order_coupon` FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`coupon_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_order_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Các ràng buộc cho bảng `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `fk_items_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_items_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`);

--
-- Các ràng buộc cho bảng `order_logs`
--
ALTER TABLE `order_logs`
  ADD CONSTRAINT `fk_log_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `order_status_history`
--
ALTER TABLE `order_status_history`
  ADD CONSTRAINT `fk_history_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`);

--
-- Các ràng buộc cho bảng `posts`
--
ALTER TABLE `posts`
  ADD CONSTRAINT `fk_post_author` FOREIGN KEY (`author_id`) REFERENCES `users` (`user_id`);

--
-- Các ràng buộc cho bảng `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `fk_product_brand` FOREIGN KEY (`brand_id`) REFERENCES `brands` (`brand_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_product_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`),
  ADD CONSTRAINT `fk_product_promotion` FOREIGN KEY (`promotion_id`) REFERENCES `promotions` (`promotion_id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `fk_images_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `product_stock_logs`
--
ALTER TABLE `product_stock_logs`
  ADD CONSTRAINT `product_stock_logs_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`);

--
-- Các ràng buộc cho bảng `return_requests`
--
ALTER TABLE `return_requests`
  ADD CONSTRAINT `fk_return_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`),
  ADD CONSTRAINT `fk_return_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Các ràng buộc cho bảng `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `fk_review_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_review_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_review_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `review_reports`
--
ALTER TABLE `review_reports`
  ADD CONSTRAINT `fk_report_review` FOREIGN KEY (`review_id`) REFERENCES `reviews` (`review_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_report_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `user_addresses`
--
ALTER TABLE `user_addresses`
  ADD CONSTRAINT `fk_address_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Các ràng buộc cho bảng `wishlists`
--
ALTER TABLE `wishlists`
  ADD CONSTRAINT `fk_wishlist_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_wishlist_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
