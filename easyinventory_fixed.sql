-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Nov 07, 2025 at 01:00 PM
-- Server version: 9.1.0
-- PHP Version: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `easyinventory1`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

DROP TABLE IF EXISTS `admin`;
CREATE TABLE IF NOT EXISTS `admin` (
  `admin_id` int NOT NULL AUTO_INCREMENT,
  `fullname` varchar(50) NOT NULL,
  `shop_name` varchar(255) NOT NULL,
  `email` varchar(200) NOT NULL,
  `password` varchar(200) NOT NULL,
  PRIMARY KEY (`admin_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`admin_id`, `fullname`, `shop_name`, `email`, `password`) VALUES
(1, 'Abhishek Sinha', 'Abhishek\'s Store', '16abhisheksinhaa@gmail.com', '$2y$10$Qrz91pwPBxjNKD03SjdPHePfJKoj3sEU9F94kx5LkOS8qt0Vh7ET.'),
(2, 'Jerry', 'Jerry Shop', 'abc@gmail.com', '$2y$10$mo1JYKvcNOLCwn82vLuJquANqVagqs5jDL8ainquUgngYQEtwUKlq'),
(3, 'Sandeep Choudhary', 'Laxmi pvt. Ltd', 'sandeeep21@gmail.com', '$2y$10$iDMABSCzH97sIucISuy99u2zrQ/GZ1QiFCuK8bAOq5b2kjA2aBH1e'),
(4, 'Chulbul Pandey', 'Pandey Store', 'pandeyji@gmail.com', '$2y$10$og6Z1AmgSojaSqXWYiq3R.acHSbDdY9BSfVrDYIsfDjXWO0H2FiBG'),
(5, 'Adip Deb', 'Deb&#039;s Store', 'Adip21@gmail.com', '$2y$10$guSe0J1KNGpDWnyHE8a3ZeL083CesrD1.LZXjpSp.TvJmWGlxu7Ge');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
CREATE TABLE IF NOT EXISTS `categories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `admin_id` int NOT NULL DEFAULT '1',
  `name` varchar(255) NOT NULL,
  `description` text,
  `gst_rate` decimal(5,2) DEFAULT '0.00',
  PRIMARY KEY (`id`),
  KEY `idx_categories_admin` (`admin_id`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `admin_id`, `name`, `description`, `gst_rate`) VALUES
(2, 1, 'Clothing', NULL, 5.00),
(3, 1, 'Electronic', NULL, 12.00),
(4, 1, 'Food', NULL, 0.00),
(17, 2, 'Food', NULL, 5.00),
(18, 2, 'Electronics', NULL, 12.00),
(19, 2, 'Clothing', NULL, 12.00),
(20, 3, 'Electronics', NULL, 18.00),
(21, 1, 'Stationary', NULL, 5.00),
(22, 2, 'Daily use', NULL, 50.00),
(23, 2, 'Fashion', NULL, 30.00);

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

DROP TABLE IF EXISTS `customers`;
CREATE TABLE IF NOT EXISTS `customers` (
  `id` int NOT NULL AUTO_INCREMENT,
  `admin_id` int NOT NULL DEFAULT '1',
  `name` varchar(255) NOT NULL,
  `street_address` varchar(255) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `pin_code` varchar(6) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_customers_admin` (`admin_id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`id`, `admin_id`, `name`, `street_address`, `city`, `pin_code`, `state`, `email`, `phone`, `address`) VALUES
(2, 1, 'abhishek', NULL, NULL, NULL, NULL, '16abhisheksinhaa@gmail.com', '09873621656', 'hedhabfjabfabddba'),
(6, 1, 'Sandeep', NULL, NULL, NULL, NULL, 'sandeep122@gmail.com', '58375983879', 'Manipur'),
(7, 2, 'Bharat', 'Nongmensong', 'Shillong', '793007', 'MEGHALAYA', 'bharatgaming2419@gmail.com', '8687473287', 'Meghalaya,shillong'),
(8, 2, 'Yooshwa', 'Laitumkhrah', 'Shillong', '793004', 'MEGHALAYA', 'yooshwa@gmail.com', '7873289463', NULL),
(9, 2, 'Adip', 'Koila', 'Agartala', '794828', 'Tripura', NULL, '8875439739', NULL),
(10, 2, 'Ankit', 'Kalinagar', 'Patna', '687983', 'Bihar', 'Ankit@gmail.com', '7453489534', NULL),
(11, 3, 'Ankit', 'Kalinagar', 'Patna', '687983', 'Bihar', 'Ankit@gmail.com', '7453489534', NULL),
(12, 1, 'Yooshwa', 'Mawprem', 'shillong', '793005', 'MEGHALAYA', 'yooshwa29@gmail.com', '9839278432', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
CREATE TABLE IF NOT EXISTS `products` (
  `id` int NOT NULL AUTO_INCREMENT,
  `admin_id` int NOT NULL DEFAULT '1',
  `name` varchar(255) NOT NULL,
  `category_id` int NOT NULL,
  `supplier_id` int DEFAULT NULL,
  `cost_price` decimal(10,2) DEFAULT '0.00' COMMENT 'What you paid to supplier (GST inclusive)',
  `has_mrp` tinyint(1) DEFAULT '0' COMMENT 'Does product have printed MRP?',
  `mrp` decimal(10,2) DEFAULT NULL COMMENT 'Maximum Retail Price (only if has_mrp=true)',
  `profit_margin` decimal(10,2) DEFAULT '0.00' COMMENT 'Your profit per unit',
  `selling_price` decimal(10,2) DEFAULT '0.00' COMMENT 'Final customer price',
  `price` decimal(10,2) DEFAULT '0.00',
  `stock` int DEFAULT '0',
  `description` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`),
  KEY `supplier_id` (`supplier_id`),
  KEY `idx_products_admin` (`admin_id`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `admin_id`, `name`, `category_id`, `supplier_id`, `cost_price`, `has_mrp`, `mrp`, `profit_margin`, `selling_price`, `price`, `stock`, `description`, `created_at`) VALUES
(4, 1, 'tv', 3, NULL, 8499.00, 1, 9999.00, 1500.00, 9999.00, 10000.00, 40, '', '2025-10-22 02:41:23'),
(9, 1, 'nike air max', 2, NULL, 8999.00, 1, 9999.00, 1000.00, 9999.00, 8999.00, 2, '', '2025-10-22 05:45:52'),
(12, 1, 'tws', 3, NULL, 1999.00, 1, 2499.00, 500.00, 2499.00, 2499.00, 8, '', '2025-10-22 07:25:39'),
(15, 1, 'HP 15s', 3, NULL, 38999.00, 1, 41999.00, 3000.00, 41999.00, 42999.00, 21, '', '2025-10-28 02:24:52'),
(17, 1, '5090', 3, NULL, 241999.00, 1, 249999.00, 6000.00, 247999.00, 249000.00, 4, ' ', '2025-10-28 03:17:59'),
(20, 2, 'Buldak ramen', 17, NULL, 110.00, 1, 140.00, 30.00, 140.00, 140.00, 110, '', '2025-10-29 15:26:22'),
(21, 2, 'Smartphones', 18, NULL, 16999.00, 1, 21999.00, 3000.00, 19999.00, 20000.00, 120, '', '2025-10-30 02:59:17'),
(22, 2, 'Mats', 19, NULL, 1999.00, 1, 2499.00, 300.00, 2299.00, 999.00, 150, '', '2025-10-30 03:03:23'),
(23, 2, 'Sony Bravia 64inch', 18, NULL, 99999.00, 1, 119999.00, 15000.00, 114999.00, 119000.00, 21, '', '2025-10-30 03:27:35'),
(24, 3, 'Macbooks m5 pro', 20, NULL, 134999.00, 1, 159999.00, 20000.00, 154999.00, 169000.00, 20, '', '2025-10-31 04:11:45'),
(26, 2, 'toothbrush', 23, NULL, 100.00, 1, 150.00, 40.00, 140.00, 0.00, 50, '', '2025-11-01 11:01:50'),
(30, 1, 'Too yum chips', 4, NULL, 15.00, 0, NULL, 5.00, 20.00, 0.00, -50, '', '2025-11-07 09:47:46');

-- --------------------------------------------------------

--
-- Table structure for table `purchases`
--

DROP TABLE IF EXISTS `purchases`;
CREATE TABLE IF NOT EXISTS `purchases` (
  `id` int NOT NULL AUTO_INCREMENT,
  `admin_id` int NOT NULL DEFAULT '1',
  `supplier_id` int NOT NULL,
  `purchase_date` date NOT NULL,
  `total_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `supplier_id` (`supplier_id`),
  KEY `idx_purchases_admin` (`admin_id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `purchases`
--

INSERT INTO `purchases` (`id`, `admin_id`, `supplier_id`, `purchase_date`, `total_amount`, `created_at`) VALUES
(1, 1, 2, '2025-10-28', 42999.00, '2025-10-28 06:30:00'),
(2, 1, 1, '2025-10-28', 17493.00, '2025-10-28 06:30:00'),
(3, 1, 14, '2025-10-28', 498000.00, '2025-10-28 06:30:00'),
(4, 2, 15, '2025-10-30', 1214000.00, '2025-10-30 06:30:00'),
(5, 2, 22, '2025-10-30', 199800.00, '2025-10-30 06:30:00'),
(7, 3, 23, '2025-10-31', 8450000.00, '2025-10-31 06:30:00'),
(11, 2, 15, '2025-11-07', 1779949.00, '2025-11-06 18:41:16');

-- --------------------------------------------------------

--
-- Table structure for table `purchase_items`
--

DROP TABLE IF EXISTS `purchase_items`;
CREATE TABLE IF NOT EXISTS `purchase_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `purchase_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` int NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `subtotal` decimal(12,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `purchase_id` (`purchase_id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `purchase_items`
--

INSERT INTO `purchase_items` (`id`, `purchase_id`, `product_id`, `quantity`, `unit_price`, `subtotal`) VALUES
(1, 1, 15, 1, 42999.00, 42999.00),
(2, 2, 12, 7, 2499.00, 17493.00),
(3, 3, 17, 2, 249000.00, 498000.00),
(4, 4, 21, 60, 20000.00, 1200000.00),
(5, 4, 20, 100, 140.00, 14000.00),
(6, 5, 22, 200, 999.00, 199800.00),
(8, 7, 24, 50, 169000.00, 8450000.00),
(13, 11, 23, 11, 99999.00, 1099989.00),
(14, 11, 21, 40, 16999.00, 679960.00);

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

DROP TABLE IF EXISTS `sales`;
CREATE TABLE IF NOT EXISTS `sales` (
  `id` int NOT NULL AUTO_INCREMENT,
  `admin_id` int NOT NULL DEFAULT '1',
  `customer_id` int DEFAULT NULL,
  `sale_date` date NOT NULL,
  `total_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `customer_id` (`customer_id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `sales`
--

INSERT INTO `sales` (`id`, `admin_id`, `customer_id`, `sale_date`, `total_amount`, `created_at`) VALUES
(1, 1, 2, '2025-10-28', 44995.00, '2025-10-28 06:30:00'),
(2, 1, 2, '2025-10-28', 188979.00, '2025-10-28 06:30:00'),
(3, 1, 6, '2025-10-28', 50000.00, '2025-10-28 06:30:00'),
(5, 2, 9, '2025-10-30', 649950.00, '2025-10-30 06:30:00'),
(6, 2, 9, '2025-10-30', 26600.00, '2025-10-30 06:30:00'),
(7, 2, 10, '2025-10-30', 5950000.00, '2025-10-30 06:30:00'),
(8, 2, 7, '2025-10-30', 200000.00, '2025-10-30 06:30:00'),
(9, 3, 11, '2025-10-31', 5070000.00, '2025-10-31 06:30:00'),
(11, 1, 12, '2025-11-01', 1743000.00, '2025-11-01 06:30:00'),
(12, 1, NULL, '2025-11-07', 1000.00, '2025-11-07 09:48:34');

-- --------------------------------------------------------

--
-- Table structure for table `sale_items`
--

DROP TABLE IF EXISTS `sale_items`;
CREATE TABLE IF NOT EXISTS `sale_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `sale_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` int NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `subtotal` decimal(12,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sale_id` (`sale_id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `sale_items`
--

INSERT INTO `sale_items` (`id`, `sale_id`, `product_id`, `quantity`, `unit_price`, `subtotal`) VALUES
(1, 1, 9, 5, 8999.00, 44995.00),
(2, 2, 9, 21, 8999.00, 188979.00),
(3, 3, 4, 5, 10000.00, 50000.00),
(5, 5, 21, 30, 20000.00, 600000.00),
(6, 5, 22, 50, 999.00, 49950.00),
(7, 6, 20, 190, 140.00, 26600.00),
(8, 7, 23, 50, 119000.00, 5950000.00),
(9, 8, 21, 10, 20000.00, 200000.00),
(10, 9, 24, 30, 169000.00, 5070000.00),
(12, 11, 17, 7, 249000.00, 1743000.00),
(13, 12, 30, 50, 20.00, 1000.00);

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

DROP TABLE IF EXISTS `suppliers`;
CREATE TABLE IF NOT EXISTS `suppliers` (
  `id` int NOT NULL AUTO_INCREMENT,
  `admin_id` int NOT NULL DEFAULT '1',
  `name` varchar(255) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `street_address` varchar(255) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `pin_code` varchar(20) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_suppliers_admin` (`admin_id`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `suppliers`
--

INSERT INTO `suppliers` (`id`, `admin_id`, `name`, `email`, `phone`, `street_address`, `city`, `pin_code`, `state`) VALUES
(1, 1, 'AGC', 'agc@gmail.com', '88827837778', NULL, NULL, NULL, NULL),
(2, 1, 'B Group', 'info@bgroup.com', '7787773787', NULL, NULL, NULL, NULL),
(3, 1, 'HP Group', 'info@hpgroup.com', '3333333343', NULL, NULL, NULL, NULL),
(14, 1, 'bharat', 'bharatgaming2419@gmail.com', '8687473287', NULL, NULL, NULL, NULL),
(15, 2, 'abhishek', '16abhisheksinhaa@gmail.com', '8732062857', 'laban', 'Shillong', '793006', 'MEGHALAYA'),
(21, 2, 'Joydeep', 'Joydeep12@gmail.com', '8839278432', 'Mawprem', 'shillong', '793005', 'MEGHALAYA'),
(22, 2, 'Salman', NULL, '7534959347', 'Khanapara', 'guwahati', '878239', 'Assam'),
(23, 3, 'abhishek', '16abhisheksinhaa@gmail.com', '8732062857', 'KENCHS TRACE', 'EAST KHASI HILLS', '793004', 'MEGHALAYA'),
(25, 1, 'Samson Hajong', 'Samson@gay.com', '8546846489', 'Barik', 'Shillong', '793001', 'MEGHALAYA');

--
-- Constraints for dumped tables
--

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `products_ibfk_2` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `purchases`
--
ALTER TABLE `purchases`
  ADD CONSTRAINT `purchases_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `purchase_items`
--
ALTER TABLE `purchase_items`
  ADD CONSTRAINT `purchase_items_ibfk_1` FOREIGN KEY (`purchase_id`) REFERENCES `purchases` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `purchase_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `sales`
--
ALTER TABLE `sales`
  ADD CONSTRAINT `sales_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `sale_items`
--
ALTER TABLE `sale_items`
  ADD CONSTRAINT `sale_items_ibfk_1` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `sale_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
