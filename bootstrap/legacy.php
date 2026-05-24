<?php

if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}

require_once BASE_PATH . '/config.php';
require_once BASE_PATH . '/core/DB.php';
require_once BASE_PATH . '/core/Log.php';

global $CONFIG;

try {
    $settingsService = new \App\Services\SettingsService();
    foreach ($settingsService->loadFromDatabase() as $key => $value) {
        $CONFIG[$key] = $value;
    }
} catch (Exception $e) {
    // Table might not exist yet or DB down
}
