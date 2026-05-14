-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th5 14, 2026 lúc 06:11 AM
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
(1, 'Chương trình Khuyến mãi mùa hè cực cháy dành cho các BOSS', 'banner_1778675644_59470.png', '', 'slider', 0, 1);

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
(1, 'Royal Canin', 'brand_1778674277_9464.png', ''),
(2, 'Pedigree', 'brand_1778674049_8407.png', ''),
(3, 'Natural Core', 'brand_1778647652_7413.webp', ''),
(4, 'Taste of the Wild', 'brand_1778674325_9906.jpg', '');

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
(1, 'Tin Tức Shop Thú Cưng - Cẩm Nang Chăm Sóc, Dinh Dưỡng Và Kinh Nghiệm Nuôi Thú Cưng Toàn Diện', 'tin-tuc-shop-thu-cung-cam-nang-cham-soc-dinh-duong-va-kinh-nghiem-nuoi-thu-cung-toan-dien', 'Chào mừng bạn đến với chuyên mục Tin Tức Shop Thú Cưng – nơi cập nhật những kiến thức hữu ích, kinh nghiệm thực tế và xu hướng mới nhất trong việc chăm sóc chó, mèo, hamster, thỏ, chim cảnh và nhiều loài thú cưng khác. Tại đây, chúng tôi tổng hợp những bài viết chuyên sâu về dinh dưỡng, sức khỏe, huấn luyện, vệ sinh, làm đẹp và lựa chọn phụ kiện phù hợp nhằm giúp thú cưng của bạn luôn khỏe mạnh, vui vẻ và phát triển toàn diện.\n1. Tại Sao Việc Chăm Sóc Thú Cưng Đúng Cách Lại Quan Trọng?\nThú cưng không chỉ là vật nuôi mà còn là những người bạn thân thiết trong gia đình. Việc chăm sóc đúng cách giúp kéo dài tuổi thọ, tăng cường sức khỏe và cải thiện chất lượng cuộc sống của các bé. Một chế độ dinh dưỡng khoa học, môi trường sống sạch sẽ và sự quan tâm thường xuyên sẽ giúp thú cưng hạn chế bệnh tật, giảm stress và phát triển ổn định cả về thể chất lẫn tinh thần.\nNgoài ra, việc hiểu rõ nhu cầu từng giống loài còn giúp chủ nuôi đưa ra lựa chọn phù hợp về thức ăn, đồ chơi, chuồng trại và các sản phẩm hỗ trợ chăm sóc hằng ngày.\n2. Cập Nhật Tin Tức Mới Nhất Về Thị Trường Thú Cưng\nNgành công nghiệp thú cưng đang phát triển mạnh mẽ với hàng loạt sản phẩm và dịch vụ mới. Các thương hiệu nổi tiếng liên tục ra mắt thức ăn hữu cơ, hạt dinh dưỡng cao cấp, pate tươi, sữa bổ sung vitamin, thuốc hỗ trợ tiêu hóa, sản phẩm trị ve rận và nhiều phụ kiện thông minh.\nBên cạnh đó, xu hướng chăm sóc thú cưng hiện đại ngày càng chú trọng đến sức khỏe tinh thần, chế độ ăn cân bằng và các hoạt động vận động phù hợp. Chủ nuôi ngày nay không chỉ quan tâm đến việc cho ăn mà còn đầu tư vào dịch vụ spa, khách sạn thú cưng, huấn luyện hành vi và bảo hiểm sức khỏe cho thú cưng.\n3. Hướng Dẫn Chọn Thức Ăn Phù Hợp\nThức Ăn Cho Chó\nChó cần chế độ ăn giàu protein, chất béo tốt, vitamin và khoáng chất. Tùy theo độ tuổi và kích thước, bạn có thể lựa chọn hạt khô, pate, thức ăn tươi hoặc chế độ ăn tự nấu. Các giống chó nhỏ thường cần hạt kích thước nhỏ và dễ tiêu hóa, trong khi chó lớn cần bổ sung glucosamine để hỗ trợ xương khớp.\nThức Ăn Cho Mèo\nMèo là động vật ăn thịt bắt buộc, do đó cần lượng đạm động vật cao. Taurine là dưỡng chất quan trọng giúp bảo vệ tim mạch và thị lực. Ngoài thức ăn khô, bạn nên kết hợp pate hoặc súp thưởng để tăng lượng nước nạp vào cơ thể.\nThức Ăn Cho Hamster, Thỏ Và Chim Cảnh\nHamster cần hạt ngũ cốc, trái cây sấy và thức ăn chuyên dụng. Thỏ cần cỏ khô Timothy, rau xanh và viên nén giàu chất xơ. Chim cảnh cần hỗn hợp hạt, trái cây và vitamin để duy trì bộ lông đẹp và sức khỏe tốt.\n4. Những Bệnh Thường Gặp Ở Thú Cưng\nMột số vấn đề phổ biến bao gồm:\n\n\nRối loạn tiêu hóa do thay đổi thức ăn đột ngột.\n\n\nViêm da, nấm và ký sinh trùng ngoài da.\n\n\nBệnh đường hô hấp.\n\n\nBéo phì do ít vận động.\n\n\nCác bệnh về răng miệng.\n\n\nStress và rối loạn hành vi.\n\n\nViệc tiêm phòng đầy đủ, tẩy giun định kỳ và khám sức khỏe thường xuyên là yếu tố then chốt để phòng ngừa bệnh tật.\n5. Cách Chăm Sóc Lông Và Da\nBộ lông khỏe mạnh phản ánh tình trạng sức khỏe tổng thể của thú cưng. Chủ nuôi nên:\n\n\nChải lông hằng ngày.\n\n\nTắm bằng sữa tắm chuyên dụng.\n\n\nSử dụng dầu dưỡng và xịt khử mùi.\n\n\nBổ sung Omega 3 và Omega 6.\n\n\nKiểm tra ve rận thường xuyên.\n\n\nĐối với những giống chó mèo lông dài, việc cắt tỉa định kỳ giúp giảm rối lông và hạn chế các bệnh về da.\n6. Phụ Kiện Cần Thiết Cho Thú Cưng\nCác sản phẩm không thể thiếu gồm:\n\n\nBát ăn và bình nước tự động.\n\n\nNhà ngủ, ổ nằm mềm mại.\n\n\nChuồng vận chuyển.\n\n\nKhay vệ sinh và cát vệ sinh.\n\n\nVòng cổ, dây dắt.\n\n\nĐồ chơi tương tác.\n\n\nCây cào móng cho mèo.\n\n\nĐệm làm mát hoặc giữ ấm.\n\n\n7. Kinh Nghiệm Huấn Luyện Cơ Bản\nHuấn luyện thú cưng giúp hình thành thói quen tốt và tăng khả năng giao tiếp giữa chủ và vật nuôi. Các kỹ năng cơ bản gồm:\n\n\nĐi vệ sinh đúng chỗ.\n\n\nNgồi, nằm, đứng yên.\n\n\nKhông cắn phá đồ đạc.\n\n\nLàm quen với dây dắt.\n\n\nPhản hồi khi được gọi tên.\n\n\nPhương pháp thưởng bằng bánh snack và lời khen thường mang lại hiệu quả cao.\n8. Xu Hướng Nuôi Thú Cưng Hiện Đại\nNgày càng nhiều gia đình coi thú cưng như một thành viên chính thức. Điều này thúc đẩy nhu cầu về:\n\n\nThức ăn hữu cơ.\n\n\nDịch vụ spa và grooming.\n\n\nBảo hiểm thú cưng.\n\n\nThiết bị thông minh theo dõi sức khỏe.\n\n\nCamera quan sát thú cưng.\n\n\nĐồ chơi kích thích trí tuệ.\n\n\n9. Mẹo Giúp Thú Cưng Sống Khỏe Mạnh\n\n\nCung cấp nước sạch liên tục.\n\n\nDuy trì lịch tiêm phòng.\n\n\nCho vận động mỗi ngày.\n\n\nKiểm soát cân nặng.\n\n\nKhám sức khỏe định kỳ.\n\n\nGiữ môi trường sống sạch sẽ.\n\n\nDành thời gian chơi và tương tác.\n\n\n10. Những Sản Phẩm Được Yêu Thích Tại Shop Thú Cưng\nTại shop thú cưng, khách hàng có thể tìm thấy hàng nghìn sản phẩm chất lượng cao như:\n\n\nThức ăn hạt cho chó mèo mọi lứa tuổi.\n\n\nPate dinh dưỡng cao cấp.\n\n\nCát vệ sinh khử mùi.\n\n\nSữa tắm trị nấm và ve rận.\n\n\nVitamin tổng hợp.\n\n\nBánh thưởng huấn luyện.\n\n\nĐồ chơi gặm nhai.\n\n\nChuồng và balo vận chuyển.\n\n\n11. Tư Vấn Chọn Sản Phẩm Theo Độ Tuổi\nThú Cưng Sơ Sinh\nCần sữa thay thế, bình bú, đệm giữ nhiệt và sản phẩm hỗ trợ miễn dịch.\nThú Cưng Trưởng Thành\nCần chế độ ăn cân bằng, đồ chơi vận động và các sản phẩm chăm sóc lông.\nThú Cưng Cao Tuổi\nCần thực phẩm hỗ trợ khớp, tim mạch và tiêu hóa.\n12. Cộng Đồng Yêu Thú Cưng\nChuyên mục tin tức cũng là nơi kết nối cộng đồng những người yêu động vật. Bạn có thể tìm thấy các câu chuyện cảm động, kinh nghiệm nhận nuôi, chia sẻ về cứu hộ động vật và các hoạt động thiện nguyện dành cho chó mèo bị bỏ rơi.\n13. Cam Kết Từ Shop Thú Cưng\nChúng tôi luôn cập nhật những kiến thức mới nhất, lựa chọn sản phẩm chính hãng và mang đến dịch vụ tư vấn tận tâm. Mỗi bài viết trong chuyên mục tin tức đều được biên soạn nhằm giúp người nuôi hiểu rõ hơn về nhu cầu của thú cưng, từ đó chăm sóc các bé một cách khoa học và hiệu quả nhất.\n14. Kết Luận\nNuôi thú cưng là một hành trình đầy niềm vui và trách nhiệm. Với nguồn thông tin phong phú từ chuyên mục Tin Tức Shop Thú Cưng, bạn sẽ có thêm kiến thức để chăm sóc người bạn bốn chân của mình tốt hơn mỗi ngày. Hãy thường xuyên theo dõi chuyên mục để cập nhật những bài viết mới nhất về dinh dưỡng, sức khỏe, huấn luyện và các sản phẩm hữu ích dành cho thú cưng thân yêu của bạn.', '2026-05-13 12:46:57');

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
(1, '1234', '1234', '123', 'post_1778663933_6722.jpg', 'blog', 1, 1, '2026-05-13 12:06:28');

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
(5, 12, 4, NULL, 'Hạt không ngũ cốc Taste of the Wild', 'DOG-FOOD-005', 550000.00, 520000.00, 'product_1778676915_37020.png', 'Phù hợp cho chó dị ứng với các loại ngũ cốc.', 9, 'active', '2026-04-27 06:35:34', '2026-05-13 12:55:27'),
(6, 13, NULL, NULL, 'Bánh thưởng mềm vị gà Bowwow', 'TREAT-001', 35000.00, 0.00, 'product_1778677786_83592.webp', 'Bánh thưởng mềm, thơm mùi gà, thích hợp để huấn luyện.', 200, 'active', '2026-04-27 06:35:34', '2026-05-13 13:09:46'),
(7, 14, NULL, NULL, 'Xương gặm sạch răng Pedigree Dentastix', 'TREAT-002', 25000.00, 0.00, 'product_1778677817_25262.png', 'Giúp giảm mảng bám và vôi răng cho cún.', 150, 'active', '2026-04-27 06:35:34', '2026-05-13 13:10:17'),
(8, 15, NULL, NULL, 'Súp thưởng Wanpy cho chó', 'TREAT-003', 12000.00, 0.00, 'product_1778677843_97112.jpg', 'Súp dạng lỏng, bổ sung nước và dưỡng chất.', 300, 'active', '2026-04-27 06:35:34', '2026-05-13 13:10:43'),
(9, 16, NULL, NULL, 'Bánh quy hình xương Milk Biscuit', 'TREAT-004', 65000.00, 0.00, 'product_1778677715_48899.webp', 'Bánh quy giòn tan, giàu canxi.', 79, 'active', '2026-04-27 06:35:34', '2026-05-13 13:08:35'),
(10, 17, NULL, NULL, 'Thịt bò sấy khô nguyên miếng', 'TREAT-005', 120000.00, 0.00, 'product_1778677898_26587.png', 'Thịt bò thật sấy lạnh, giữ nguyên hương vị.', 40, 'active', '2026-04-27 06:35:34', '2026-05-13 13:11:38'),
(11, 25, NULL, NULL, 'Vòng cổ vải dù phản quang', 'ACC-001', 50000.00, 0.00, NULL, 'Dây dù bền chắc, có phản quang an toàn khi đi đêm.', 60, 'active', '2026-04-27 06:35:34', '2026-04-27 06:35:34');

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
(2, 'user', 'user@gmail.com', '$2y$10$wI8Zjf14MZi/J2oPumHit.0j2w67xHK4cBC.t3BzKGrcd9ohdrfdK', 'Trần User', '0987654731', 'Lý Tự Trọng\r\nNinh Kiều, Phường An Khánh, Quận Ninh Kiều, Thành phố Cần Thơ', 'default-avatar.png', 'customer', 1, NULL, '2026-04-30 13:24:17');

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
  MODIFY `banner_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT cho bảng `brands`
--
ALTER TABLE `brands`
  MODIFY `brand_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT cho bảng `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT cho bảng `coupons`
--
ALTER TABLE `coupons`
  MODIFY `coupon_id` int(11) NOT NULL AUTO_INCREMENT;

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
  MODIFY `page_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT cho bảng `posts`
--
ALTER TABLE `posts`
  MODIFY `post_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT cho bảng `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT cho bảng `product_images`
--
ALTER TABLE `product_images`
  MODIFY `image_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `product_stock_logs`
--
ALTER TABLE `product_stock_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT;

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
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

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
