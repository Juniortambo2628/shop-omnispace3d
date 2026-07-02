-- OmniShop local database schema
-- Run: mysql -u root < database/schema.sql
-- Or:  php artisan db:setup

CREATE DATABASE IF NOT EXISTS omnishop_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE omnishop_db;

CREATE TABLE IF NOT EXISTS admin_users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    display_name VARCHAR(255) NOT NULL,
    role VARCHAR(50) NOT NULL DEFAULT 'order_manager',
    active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY admin_users_username_unique (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS admin_products (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    prod_id VARCHAR(100) NOT NULL,
    code VARCHAR(100) NOT NULL,
    name VARCHAR(255) NOT NULL,
    category_id VARCHAR(50) NOT NULL,
    colors JSON NULL,
    dimensions VARCHAR(255) NULL,
    price DECIMAL(12, 2) NOT NULL DEFAULT 0,
    price_display VARCHAR(100) NULL,
    description TEXT NULL,
    unit VARCHAR(100) NOT NULL DEFAULT 'per item',
    is_poa TINYINT(1) NOT NULL DEFAULT 0,
    is_override TINYINT(1) NOT NULL DEFAULT 0,
    original_catalog_id VARCHAR(100) NULL,
    active TINYINT(1) NOT NULL DEFAULT 1,
    created_by VARCHAR(100) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY admin_products_prod_id_unique (prod_id),
    KEY admin_products_code_index (code),
    KEY admin_products_active_index (active),
    KEY admin_products_category_id_index (category_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS orders (
    id VARCHAR(20) NOT NULL PRIMARY KEY,
    custom_order_id VARCHAR(20) NULL,
    event_slug VARCHAR(100) NOT NULL,
    company_name VARCHAR(255) NOT NULL,
    contact_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(50) NULL,
    address TEXT NULL,
    tax_id VARCHAR(100) NULL,
    booth_number VARCHAR(50) NOT NULL,
    special_instructions TEXT NULL,
    payment_method VARCHAR(50) NOT NULL,
    subtotal DECIMAL(12, 2) NOT NULL DEFAULT 0,
    vat DECIMAL(12, 2) NOT NULL DEFAULT 0,
    total DECIMAL(12, 2) NOT NULL DEFAULT 0,
    status VARCHAR(50) NOT NULL DEFAULT 'Pending',
    payment_reference VARCHAR(255) NULL,
    client_payment_reference VARCHAR(255) NULL,
    payment_verification_status ENUM('unverified','pending','verified','rejected') NOT NULL DEFAULT 'unverified',
    payment_verified_at DATETIME NULL,
    payment_verified_by VARCHAR(100) NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    UNIQUE KEY orders_custom_order_id_unique (custom_order_id),
    KEY orders_event_slug_index (event_slug),
    KEY orders_status_index (status),
    KEY orders_created_at_index (created_at),
    KEY orders_email_index (email),
    KEY orders_booth_number_index (booth_number),
    KEY orders_company_name_index (company_name),
    KEY orders_event_slug_custom_order_id_index (event_slug, custom_order_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS order_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id VARCHAR(20) NOT NULL,
    product_id VARCHAR(100) NULL,
    product_name VARCHAR(255) NOT NULL,
    product_code VARCHAR(100) NULL,
    category VARCHAR(100) NULL,
    color_id VARCHAR(20) NULL,
    color_name VARCHAR(100) NULL,
    quantity INT UNSIGNED NOT NULL DEFAULT 1,
    unit_price DECIMAL(12, 2) NOT NULL DEFAULT 0,
    total_price DECIMAL(12, 2) NOT NULL DEFAULT 0,
    KEY order_items_order_id_index (order_id),
    KEY order_items_product_code_index (product_code),
    KEY order_items_product_id_index (product_id),
    KEY order_items_category_index (category),
    CONSTRAINT order_items_order_id_foreign
        FOREIGN KEY (order_id) REFERENCES orders (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS stock_levels (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_code VARCHAR(100) NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    stock_limit INT NULL DEFAULT NULL,
    UNIQUE KEY stock_levels_product_code_unique (product_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS settings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `key` VARCHAR(191) NOT NULL,
    `value` TEXT NULL,
    UNIQUE KEY settings_key_unique (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS sessions (
    id VARCHAR(128) NOT NULL PRIMARY KEY,
    user_id INT UNSIGNED NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    payload LONGTEXT NOT NULL,
    last_activity INT NOT NULL,
    KEY sessions_user_id_index (user_id),
    KEY sessions_last_activity_index (last_activity)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
