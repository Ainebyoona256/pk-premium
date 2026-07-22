-- ===================================================
-- PK PREMIUM STYLES AND SCENTS - Database Setup
-- Host: sql200.ezyro.com
-- Database: ezyro_42471412_pkpremium
-- ===================================================

-- Create database (run this first if database doesn't exist)
-- CREATE DATABASE IF NOT EXISTS ezyro_42471412_pkpremium CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- USE ezyro_42471412_pkpremium;

-- Settings table (subscription management)
CREATE TABLE IF NOT EXISTS `settings` (
    `id` INT PRIMARY KEY DEFAULT 1,
    `subscription_expiry` DATE NOT NULL DEFAULT '2026-12-31'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Admin table
CREATE TABLE IF NOT EXISTS `admin` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Categories table
CREATE TABLE IF NOT EXISTS `categories` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `image_url` VARCHAR(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Products table
CREATE TABLE IF NOT EXISTS `products` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `category_id` INT NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `description` TEXT,
    `price` DECIMAL(10,2) NOT NULL,
    `discount_price` DECIMAL(10,2) DEFAULT 0,
    `quantity_remaining` INT DEFAULT 0,
    `image_url` VARCHAR(500) DEFAULT NULL,
    FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Offers table
CREATE TABLE IF NOT EXISTS `offers` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL,
    `discount_percent` INT NOT NULL,
    `start_date` DATE NOT NULL,
    `end_date` DATE NOT NULL,
    `is_active` TINYINT(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default settings
INSERT IGNORE INTO `settings` (id, subscription_expiry) VALUES (1, '2026-12-31');

-- Insert default admin (password: Phionah@26)
-- Auto-generate the hash by running: php -r "echo password_hash('Phionah@26', PASSWORD_DEFAULT);"
-- Then replace the hash below with the generated one.
INSERT IGNORE INTO `admin` (id, email, password) VALUES (1, 'ntunguraphionahk@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Insert default categories
INSERT IGNORE INTO `categories` (id, name, image_url) VALUES
(1, 'CLOTHES', ''),
(2, 'SHOES', ''),
(3, 'JEWELLERY', ''),
(4, 'DEODORANTS', ''),
(5, 'BODY SPRAYS', '');

-- Indexes for faster queries
CREATE INDEX IF NOT EXISTS idx_products_category ON `products`(`category_id`);
CREATE INDEX IF NOT EXISTS idx_offers_dates ON `offers`(`start_date`, `end_date`, `is_active`);
