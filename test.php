<?php
echo "<h1>OmniShop Diagnostic Test</h1>";
echo "PHP Version: " . PHP_VERSION . "<br>";
echo "Current Directory: " . __DIR__ . "<br>";

$base_path = defined('BASE_PATH') ? BASE_PATH : __DIR__;
echo "Target Base Path: " . $base_path . "<br>";

if (file_exists($base_path . '/config.php')) {
    echo "✅ config.php found<br>";
} else {
    echo "❌ config.php NOT found at " . $base_path . "/config.php<br>";
}

if (file_exists($base_path . '/vendor/autoload.php')) {
    echo "✅ vendor/autoload.php found<br>";
} else {
    echo "❌ vendor/autoload.php NOT found. You need to run 'composer install'<br>";
}

if (file_exists($base_path . '/core/DB.php')) {
    echo "✅ core/DB.php found<br>";
} else {
    echo "❌ core/DB.php NOT found<br>";
}
