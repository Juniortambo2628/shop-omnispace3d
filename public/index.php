<?php

define('LARAVEL_START', microtime(true));

if (! defined('BASE_PATH')) {
    $productionCore = __DIR__ . '/../../solar-and-storage-core';

    if (is_file($productionCore . '/artisan')) {
        define('BASE_PATH', $productionCore);
    } else {
        define('BASE_PATH', dirname(__DIR__));
    }
}

if (file_exists($maintenance = BASE_PATH . '/storage/framework/maintenance.php')) {
    require $maintenance;
}

require BASE_PATH . '/vendor/autoload.php';

$app = require_once BASE_PATH . '/bootstrap/app.php';

$app->handleRequest(Illuminate\Http\Request::capture());
