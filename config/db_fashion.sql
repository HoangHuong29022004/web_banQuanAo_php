-- Xóa các bảng cũ nếu tồn tại
DROP TABLE IF EXISTS order_details;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS carts;
DROP TABLE IF EXISTS product_variants;
DROP TABLE IF EXISTS product_images;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS users;

-- Tạo lại các bảng
CREATE TABLE `categories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text,
  `parent_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `products` (
  `id` int NOT NULL AUTO_INCREMENT,
  `category_id` int DEFAULT NULL,
  `name` varchar(200) NOT NULL,
  `description` text,
  `price` decimal(10,2) NOT NULL,
  `sale_price` decimal(10,2) DEFAULT NULL,
  `stock` int DEFAULT '0',
  `featured` tinyint(1) DEFAULT '0',
  `views` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `product_images` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` int NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `is_main` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `product_variants` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` int NOT NULL,
  `size` varchar(10) NOT NULL,
  `color` varchar(50) NOT NULL,
  `stock` int DEFAULT '0',
  `sku` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `fullname` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text,
  `role` enum('user','admin') DEFAULT 'user',
  `status` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `carts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `product_variant_id` int NOT NULL,
  `quantity` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `product_variant_id` (`product_variant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `orders` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','processing','shipping','completed','cancelled') DEFAULT 'pending',
  `payment_method` varchar(50) NOT NULL,
  `payment_status` enum('pending','paid','failed') DEFAULT 'pending',
  `shipping_address` text NOT NULL,
  `shipping_phone` varchar(20) NOT NULL,
  `note` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `order_details` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `product_variant_id` int NOT NULL,
  `quantity` int NOT NULL,
  `price` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `product_variant_id` (`product_variant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Thêm các ràng buộc khóa ngoại
ALTER TABLE `categories`
  ADD CONSTRAINT `categories_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

ALTER TABLE `product_images`
  ADD CONSTRAINT `product_images_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

ALTER TABLE `product_variants`
  ADD CONSTRAINT `product_variants_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

ALTER TABLE `carts`
  ADD CONSTRAINT `carts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `carts_ibfk_2` FOREIGN KEY (`product_variant_id`) REFERENCES `product_variants` (`id`) ON DELETE CASCADE;

ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

ALTER TABLE `order_details`
  ADD CONSTRAINT `order_details_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_details_ibfk_2` FOREIGN KEY (`product_variant_id`) REFERENCES `product_variants` (`id`) ON DELETE CASCADE;

-- Thêm dữ liệu cho bảng categories
INSERT INTO `categories` (`name`, `description`, `parent_id`) VALUES
-- Danh mục Quần
('Quần', 'Các loại quần thời trang', NULL),
('Quần Jean', 'Quần jean thời trang', 1),
('Quần Short', 'Quần short năng động', 1),
('Quần Tây', 'Quần tây công sở', 1),
('Quần Baggy', 'Quần baggy thời trang', 1),

-- Danh mục Áo
('Áo', 'Các loại áo thời trang', NULL),
('Áo Thun', 'Áo thun basic và in họa tiết', 6),
('Áo Sơ Mi', 'Áo sơ mi công sở và casual', 6),
('Áo Khoác', 'Áo khoác các loại', 6),
('Áo Len', 'Áo len và sweater', 6),

-- Danh mục Váy
('Váy', 'Các loại váy thời trang', NULL),
('Váy Ngắn', 'Váy ngắn thời trang', 11),
('Váy Dài', 'Váy dài thướt tha', 11),
('Chân Váy', 'Chân váy các loại', 11),
('Đầm', 'Đầm một mảnh', 11);

-- Thêm dữ liệu cho bảng products
INSERT INTO `products` (`category_id`, `name`, `description`, `price`, `sale_price`, `stock`, `featured`) VALUES
-- Quần
(2, 'Quần Jean Nữ Ống Rộng', 'Quần jean nữ ống rộng phong cách Hàn Quốc', 450000, 399000, 100, 1),
(2, 'Quần Jean Nữ Skinny', 'Quần jean nữ ôm sát năng động', 380000, NULL, 80, 1),
(3, 'Quần Short Jean Nữ', 'Quần short jean nữ năng động', 250000, 199000, 120, 0),
(4, 'Quần Tây Nữ Công Sở', 'Quần tây nữ công sở thanh lịch', 420000, NULL, 50, 1),
(5, 'Quần Baggy Nữ Kẻ Sọc', 'Quần baggy nữ kẻ sọc thời trang', 350000, 299000, 70, 1),

-- Áo
(7, 'Áo Thun Basic Nữ', 'Áo thun trơn form rộng basic', 199000, NULL, 150, 1),
(7, 'Áo Thun In Họa Tiết', 'Áo thun in họa tiết cute', 250000, 199000, 100, 1),
(8, 'Áo Sơ Mi Trắng Công Sở', 'Áo sơ mi trắng công sở basic', 320000, NULL, 80, 1),
(9, 'Áo Khoác Denim', 'Áo khoác jean thời trang', 550000, 499000, 60, 1),
(10, 'Áo Len Dệt Kim', 'Áo len dệt kim mỏng', 399000, NULL, 70, 1),

-- Váy
(12, 'Váy Tennis Xếp Ly', 'Váy tennis xếp ly năng động', 320000, 279000, 90, 1),
(13, 'Váy Maxi Hoa', 'Váy maxi hoa dài thướt tha', 550000, NULL, 40, 1),
(14, 'Chân Váy Xếp Ly', 'Chân váy xếp ly công sở', 280000, 249000, 85, 1),
(15, 'Đầm Suông Công Sở', 'Đầm suông công sở thanh lịch', 480000, NULL, 55, 1),
(15, 'Đầm Hoa Nhí', 'Đầm hoa nhí dễ thương', 420000, 379000, 65, 1);

-- Thêm dữ liệu cho bảng product_images
INSERT INTO `product_images` (`product_id`, `image_path`, `is_main`) VALUES
(1, 'sp01.jpg', 1),
(2, 'sp02.jpg', 1),
(3, 'sp03.jpg', 1),
(4, 'sp04.jpg', 1),
(5, 'sp05.jpg', 1),
(6, 'sp06.jpg', 1),
(7, 'sp07.jpg', 1),
(8, 'sp08.jpg', 1);

-- Thêm dữ liệu cho bảng product_variants
INSERT INTO `product_variants` (`product_id`, `size`, `color`, `stock`) VALUES
-- Quần Jean Nữ Ống Rộng
(1, 'S', 'Xanh nhạt', 30),
(1, 'M', 'Xanh nhạt', 40),
(1, 'L', 'Xanh nhạt', 30),
(1, 'S', 'Xanh đậm', 25),
(1, 'M', 'Xanh đậm', 35),

-- Áo Thun Basic
(6, 'S', 'Trắng', 50),
(6, 'M', 'Trắng', 50),
(6, 'L', 'Trắng', 50),
(6, 'S', 'Đen', 50),
(6, 'M', 'Đen', 50),

-- Váy Tennis
(11, 'S', 'Đen', 30),
(11, 'M', 'Đen', 30),
(11, 'S', 'Trắng', 30),
(11, 'M', 'Trắng', 30),
(11, 'L', 'Trắng', 30);

-- Thêm dữ liệu cho bảng users
INSERT INTO `users` (`fullname`, `email`, `password`, `phone`, `address`, `role`) VALUES
('Admin', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0901234567', 'TP.HCM', 'admin'),
('Nguyễn Thị Hồng', 'hong@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0912345678', 'Hà Nội', 'user'),
('Trần Văn An', 'an@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0923456789', 'Đà Nẵng', 'user');

-- Thêm dữ liệu cho bảng carts
INSERT INTO `carts` (`user_id`, `product_variant_id`, `quantity`) VALUES
(2, 1, 1),
(2, 5, 2),
(3, 3, 1);

-- Thêm dữ liệu cho bảng orders
INSERT INTO `orders` (`user_id`, `total_amount`, `status`, `payment_method`, `payment_status`, `shipping_address`, `shipping_phone`, `note`) VALUES
(2, 848000, 'completed', 'COD', 'paid', 'Số 123 Đường ABC, Quận 1, Hà Nội', '0912345678', 'Giao giờ hành chính'),
(3, 399000, 'shipping', 'Banking', 'paid', 'Số 456 Đường XYZ, Quận Hải Châu, Đà Nẵng', '0923456789', NULL);

-- Thêm dữ liệu cho bảng order_details
INSERT INTO `order_details` (`order_id`, `product_variant_id`, `quantity`, `price`) VALUES
(1, 1, 1, 399000),
(1, 5, 1, 449000),
(2, 3, 1, 399000); 