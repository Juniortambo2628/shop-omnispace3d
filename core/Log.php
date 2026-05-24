<?php
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class Log {
    private static $logger = null;

    private static function init() {
        if (self::$logger === null) {
            self::$logger = new Logger('omnishop');
            $logPath = __DIR__ . '/../logs/app.log';
            if (!is_dir(dirname($logPath))) {
                mkdir(dirname($logPath), 0777, true);
            }
            self::$logger->pushHandler(new StreamHandler($logPath, Logger::DEBUG));
        }
    }

    public static function info($message, array $context = []) {
        self::init();
        self::$logger->info($message, $context);
    }

    public static function error($message, array $context = []) {
        self::init();
        self::$logger->error($message, $context);
    }

    public static function debug($message, array $context = []) {
        self::init();
        self::$logger->debug($message, $context);
    }
}
