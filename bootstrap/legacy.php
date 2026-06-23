<?php

if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}

require_once BASE_PATH . '/config.php';

global $CONFIG;

try {
    $settingsService = new \App\Services\SettingsService();
    foreach ($settingsService->loadFromDatabase() as $key => $value) {
        $CONFIG[$key] = $value;
    }
} catch (Exception $e) {
    // Table might not exist yet or DB down
}
