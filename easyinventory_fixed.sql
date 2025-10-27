
-- Fixed EasyInventory SQL
-- Creates database and normalized tables with purchase_items and sale_items
CREATE DATABASE IF NOT EXISTS `easyinventory1` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
USE `easyinventory1`;

-- categories
DROP TABLE IF EXISTS categories;
CREATE TABLE categories (
  id INT NOT NULL PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  description TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- suppliers
DROP TABLE IF EXISTS suppliers;
CREATE TABLE suppliers (
  id INT NOT NULL PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  email VARCHAR(100),
  phone VARCHAR(20),
  address VARCHAR(255)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- customers
DROP TABLE IF EXISTS customers;
CREATE TABLE customers (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  email VARCHAR(100),
  phone VARCHAR(20),
  address VARCHAR(255)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- products
DROP TABLE IF EXISTS products;
CREATE TABLE products (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  category_id INT NOT NULL,
  supplier_id INT DEFAULT NULL,
  price DECIMAL(10,2) DEFAULT 0.00,
  stock INT DEFAULT 0,
  description TEXT,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT ON UPDATE CASCADE,
  FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- purchases (metadata)
DROP TABLE IF EXISTS purchases;
CREATE TABLE purchases (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  supplier_id INT NOT NULL,
  purchase_date DATE NOT NULL,
  total_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- purchase_items
DROP TABLE IF EXISTS purchase_items;
CREATE TABLE purchase_items (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  purchase_id INT NOT NULL,
  product_id INT NOT NULL,
  quantity INT NOT NULL,
  unit_price DECIMAL(10,2) NOT NULL,
  subtotal DECIMAL(12,2) NOT NULL,
  FOREIGN KEY (purchase_id) REFERENCES purchases(id) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- sales (metadata)
DROP TABLE IF EXISTS sales;
CREATE TABLE sales (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  customer_id INT DEFAULT NULL,
  sale_date DATE NOT NULL,
  total_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- sale_items
DROP TABLE IF EXISTS sale_items;
CREATE TABLE sale_items (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  sale_id INT NOT NULL,
  product_id INT NOT NULL,
  quantity INT NOT NULL,
  unit_price DECIMAL(10,2) NOT NULL,
  subtotal DECIMAL(12,2) NOT NULL,
  FOREIGN KEY (sale_id) REFERENCES sales(id) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert seed categories (from original dump)
INSERT INTO categories (id, name, description) VALUES
(1, 'Tech', ''),
(2, 'Fashion', ''),
(3, 'Electronic', ''),
(4, 'food', 'dshufhfse'),
(5, 'Stationery', 'Office stationery'),
(6, 'Clothing', 'Apparel and accessories');

-- Insert suppliers (from original dump)
INSERT INTO suppliers (id, name, email, phone, address) VALUES
(1, 'AGC', 'agc@gmail.com', '88827837778', 'Shillong'),
(2, 'B Group', 'info@bgroup.com', '7787773787', 'Guwahati'),
(3, 'HP Group', 'info@hpgroup.com', '3333333343', 'Manipur'),
(9, 'abhishek', '16abhisheksinhaa@gmail.com', '9873621', 'hedhabfjabfabddba');

-- Insert products (from original dump). Note: supplier_id set to NULL in original dump; kept NULL here.
INSERT INTO products (id, name, category_id, price, stock, description, created_at, supplier_id) VALUES
(4, 'tv', 2, 10000.00, 45, '', '2025-10-22 08:11:23', NULL),
(9, 'nike air max', 3, 8999.00, 28, '', '2025-10-22 11:15:52', NULL),
(12, 'tws', 4, 2499.00, 1, '', '2025-10-22 12:55:39', NULL),
(14, 'hbdfbhjf', 3, 3434.00, 3343433, '', '2025-10-24 06:52:18', NULL);

-- (Optional) You can add customers manually or import them later.
