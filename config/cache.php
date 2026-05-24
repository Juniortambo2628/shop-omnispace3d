<?php

$config = require dirname(__DIR__) . '/vendor/laravel/framework/config/cache.php';

// Hybrid app has no cache table migration; file cache works for rate limiting + framework defaults.
$config['default'] = env('CACHE_STORE', 'file');

return $config;
