<?php
// Load .env
if (class_exists('Dotenv\Dotenv')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->safeLoad();
}

// Environment Detection
$is_prod = (strpos($_SERVER['HTTP_HOST'] ?? '', 'omnispace3d.com') !== false);
define('IS_PROD', $is_prod);

if (IS_PROD) {
    define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
    define('DB_NAME', getenv('DB_NAME') ?: 'omnispac_shop_laravel');
    define('DB_USER', getenv('DB_USER') ?: 'omnispac_omnispac_omnishop-dev');
    define('DB_PASS', getenv('DB_PASS') ?: '');
    define('STATIC_PATH', __DIR__ . '/../public_html/shop/static');
} else {
    define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
    define('DB_NAME', getenv('DB_NAME') ?: 'omnishop_db');
    define('DB_USER', getenv('DB_USER') ?: 'root');
    define('DB_PASS', getenv('DB_PASS') ?: '');
    define('STATIC_PATH', __DIR__ . '/static');
}

// Site config
define('SITE_NAME', 'OmniShop');

$CONFIG = [
    "paypal_email" => "susan@susanmboya.com",
    "admin_password" => "Silversky#10",
    "contact_email" => "solarandstoragelive@omnispace3d.com",
    "contact_phone" => "+254 731 001 723 | +254 769 361 804",
    "website" => "www.omnispace3d.com",
    "vat_rate" => 16,
];

// Roles
define('ROLE_LABELS', [
    'super_admin'    => 'Super Admin',
    'product_editor' => 'Product Editor',
    'order_manager'  => 'Order Manager',
]);

// Events
define('EVENTS', [
    "solarandstorage" => [
        "name" => "Solar and Storage Live Kenya 2026",
        "short_name" => "SSL Kenya 2026",
        "dates" => "August 26-27, 2026",
        "venue" => "Kenyatta International Convention Center, Nairobi, Kenya",
        "logo" => "/static/images/ssl-kenya-logo.png",
        "contact_email" => "solarandstoragelive@omnispace3d.com",
        "catalog_password" => "ssl2026",
        "deadlines" => [
            ["category" => "Furniture, Audiovisual & Accessories", "deadline" => "August 15, 2026"],
            ["category" => "Flooring (Basic)",                      "deadline" => "August 20, 2026"],
            ["category" => "Staffing & Services",                   "deadline" => "August 14, 2026"],
            ["category" => "Booth Branding (Standard)",             "deadline" => "July 2, 2026"],
            ["category" => "Booth Branding (Rush Orders)",          "deadline" => "August 7, 2026"],
            ["category" => "Custom Stand Builds",                   "deadline" => "July 15, 2026"],
        ]
    ]
]);

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
