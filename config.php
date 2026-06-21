<?php
// Load .env
if (class_exists('Dotenv\Dotenv')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->safeLoad();
}

if (!function_exists('env_value')) {
    /**
     * Read env vars loaded by Dotenv (phpdotenv v5 does not always populate getenv() in CLI).
     */
    function env_value(string $key, $default = null)
    {
        if (isset($_ENV[$key]) && $_ENV[$key] !== '') {
            return $_ENV[$key];
        }
        if (isset($_SERVER[$key]) && $_SERVER[$key] !== '') {
            return $_SERVER[$key];
        }
        $fromGetenv = getenv($key);
        if ($fromGetenv !== false && $fromGetenv !== '') {
            return $fromGetenv;
        }

        return $default;
    }
}

// Environment Detection (HTTP host, APP_ENV, or production server layout)
$is_prod = (strpos($_SERVER['HTTP_HOST'] ?? '', 'omnispace3d.com') !== false)
    || env_value('APP_ENV') === 'production'
    || is_dir(__DIR__ . '/../public_html/shop');
define('IS_PROD', $is_prod);

if (IS_PROD) {
    define('DB_HOST', env_value('DB_HOST', 'localhost'));
    define('DB_NAME', env_value('DB_NAME', 'omnispac_shop_laravel'));
    define('DB_USER', env_value('DB_USER', 'omnispac_omnispac_omnishop-dev'));
    define('DB_PASS', env_value('DB_PASS', ''));
    define('STATIC_PATH', __DIR__ . '/../public_html/shop/static');
} else {
    define('DB_HOST', env_value('DB_HOST', 'localhost'));
    define('DB_NAME', env_value('DB_NAME', 'omnishop_db'));
    define('DB_USER', env_value('DB_USER', 'root'));
    define('DB_PASS', env_value('DB_PASS', ''));
    define('STATIC_PATH', __DIR__ . '/static');
}

// Site config
define('SITE_NAME', 'OmniShop');

$CONFIG = [
    "paypal_email" => "susan@susanmboya.com",
    "admin_password" => "Silversky#10",
    "contact_email" => "solarandstoragelive@omnispace3d.com",
    "contact_phone" => "+254 731 001 723",
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
        "short_code" => "SSL26",
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
