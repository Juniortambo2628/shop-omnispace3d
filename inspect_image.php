<?php
require_once __DIR__ . '/vendor/autoload.php';
use Intervention\Image\ImageManager;

header('Content-Type: text/plain');

echo "PHP Version: " . PHP_VERSION . "\n";
echo "ImageManager Class Exists: " . (class_exists('Intervention\Image\ImageManager') ? 'Yes' : 'No') . "\n";

if (class_exists('Intervention\Image\ImageManager')) {
    $ref = new ReflectionClass('Intervention\Image\ImageManager');
    echo "File: " . $ref->getFileName() . "\n";
    
    $ctor = $ref->getConstructor();
    if ($ctor) {
        echo "Constructor Params:\n";
        foreach ($ctor->getParameters() as $p) {
            echo " - " . $p->getName() . " (Type: " . $p->getType() . ")\n";
        }
    } else {
        echo "No Constructor\n";
    }
    
    echo "Methods:\n";
    $methods = ['read', 'make', 'gd', 'imagick'];
    foreach ($methods as $m) {
        echo " - $m: " . (method_exists('Intervention\Image\ImageManager', $m) ? 'Yes' : 'No') . "\n";
    }
}

echo "\nInterfaces:\n";
$interfaces = [
    'Intervention\Image\Interfaces\ImageInterface',
    'Intervention\Image\Interfaces\DriverInterface'
];
foreach ($interfaces as $i) {
    echo " - $i: " . (interface_exists($i) ? 'Yes' : 'No') . "\n";
}
