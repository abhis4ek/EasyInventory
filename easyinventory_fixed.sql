-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Oct 31, 2025 at 03:48 AM
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
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`admin_id`, `fullname`, `shop_name`, `email`, `password`) VALUES
(1, 'abhishek', '', '16abhisheksinhaa@gmail.com', '$2y$10$Qrz91pwPBxjNKD03SjdPHePfJKoj3sEU9F94kx5LkOS8qt0Vh7ET.'),
(2, 'Jerry', '', 'abc@gmail.com', '$2y$10$mo1JYKvcNOLCwn82vLuJquANqVagqs5jDL8ainquUgngYQEtwUKlq'),
(3, 'Sandeep Choudhary', 'Laxmi pvt. Ltd', 'sandeeep21@gmail.com', '$2y$10$iDMABSCzH97sIucISuy99u2zrQ/GZ1QiFCuK8bAOq5b2kjA2aBH1e');

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
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `admin_id`, `name`, `description`, `gst_rate`) VALUES
(2, 1, 'Clothing', NULL, 5.00),
(3, 1, 'Electronic', NULL, 12.00),
(4, 1, 'Food', NULL, 0.00),
(17, 2, 'Food', NULL, 5.00),
(18, 2, 'Electronics', NULL, 12.00),
(19, 2, 'Clothing', NULL, 12.00);

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
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`id`, `admin_id`, `name`, `street_address`, `city`, `pin_code`, `state`, `email`, `phone`, `address`) VALUES
(1, 1, 'n mnnmn', NULL, NULL, NULL, NULL, 'n nm', 'nmn', 'nnm'),
(2, 1, 'abhishek', NULL, NULL, NULL, NULL, '16abhisheksinhaa@gmail.com', '09873621656', 'hedhabfjabfabddba'),
(6, 1, 'Sandeep', NULL, NULL, NULL, NULL, 'sandeep122@gmail.com', '58375983879', 'Manipur'),
(7, 2, 'Bharat', 'Nongmensong', 'Shillong', '793007', 'MEGHALAYA', 'bharatgaming2419@gmail.com', '8687473287', 'Meghalaya,shillong'),
(8, 2, 'Yooshwa', 'Laitumkhrah', 'Shillong', '793004', 'MEGHALAYA', 'yooshwa@gmail.com', '7873289463', NULL),
(9, 2, 'Adip', 'Koila', 'Agartala', '794828', 'Tripura', NULL, '8875439739', NULL),
(10, 2, 'Ankit', 'Kalinagar', 'Patna', '687983', 'Bihar', 'Ankit@gmail.com', '7453489534', NULL);

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
  `price` decimal(10,2) DEFAULT '0.00',
  `stock` int DEFAULT '0',
  `description` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`),
  KEY `supplier_id` (`supplier_id`),
  KEY `idx_products_admin` (`admin_id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `admin_id`, `name`, `category_id`, `supplier_id`, `price`, `stock`, `description`, `created_at`) VALUES
(4, 1, 'tv', 2, NULL, 10000.00, 40, '', '2025-10-22 02:41:23'),
(9, 1, 'nike air max', 3, NULL, 8999.00, 2, '', '2025-10-22 05:45:52'),
(12, 1, 'tws', 4, NULL, 2499.00, 8, '', '2025-10-22 07:25:39'),
(15, 1, 'HP 15s', 3, NULL, 42999.00, 21, '0', '2025-10-28 02:24:52'),
(17, 1, '5090', 3, NULL, 249000.00, 11, '0', '2025-10-28 03:17:59'),
(20, 2, 'Buldak ramen', 17, NULL, 140.00, 210, '', '2025-10-29 15:26:22'),
(21, 2, 'Smartphones', 18, NULL, 20000.00, 80, '', '2025-10-30 02:59:17'),
(22, 2, 'Mats', 19, NULL, 999.00, 150, '', '2025-10-30 03:03:23'),
(23, 2, 'Sony Bravia 64inch', 18, NULL, 119000.00, 10, '', '2025-10-30 03:27:35');

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
  PRIMARY KEY (`id`),
  KEY `supplier_id` (`supplier_id`),
  KEY `idx_purchases_admin` (`admin_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `purchases`
--

INSERT INTO `purchases` (`id`, `admin_id`, `supplier_id`, `purchase_date`, `total_amount`) VALUES
(1, 1, 2, '2025-10-28', 42999.00),
(2, 1, 1, '2025-10-28', 17493.00),
(3, 1, 14, '2025-10-28', 498000.00),
(4, 2, 15, '2025-10-30', 1214000.00),
(5, 2, 22, '2025-10-30', 199800.00),
(6, 2, 15, '2025-10-30', 28000.00);

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
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
(7, 6, 20, 200, 140.00, 28000.00);

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
  PRIMARY KEY (`id`),
  KEY `customer_id` (`customer_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `sales`
--

INSERT INTO `sales` (`id`, `admin_id`, `customer_id`, `sale_date`, `total_amount`) VALUES
(1, 1, 2, '2025-10-28', 44995.00),
(2, 1, 2, '2025-10-28', 188979.00),
(3, 1, 6, '2025-10-28', 50000.00),
(4, 2, 7, '2025-10-30', 14000.00),
(5, 2, 9, '2025-10-30', 649950.00),
(6, 2, 9, '2025-10-30', 26600.00),
(7, 2, 10, '2025-10-30', 5950000.00),
(8, 2, 7, '2025-10-30', 200000.00);

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
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `sale_items`
--

INSERT INTO `sale_items` (`id`, `sale_id`, `product_id`, `quantity`, `unit_price`, `subtotal`) VALUES
(1, 1, 9, 5, 8999.00, 44995.00),
(2, 2, 9, 21, 8999.00, 188979.00),
(3, 3, 4, 5, 10000.00, 50000.00),
(4, 4, 20, 100, 140.00, 14000.00),
(5, 5, 21, 30, 20000.00, 600000.00),
(6, 5, 22, 50, 999.00, 49950.00),
(7, 6, 20, 190, 140.00, 26600.00),
(8, 7, 23, 50, 119000.00, 5950000.00),
(9, 8, 21, 10, 20000.00, 200000.00);

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
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
(22, 2, 'Salman', NULL, '7534959347', 'Khanapara', 'guwahati', '878239', 'Assam');

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
