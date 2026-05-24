<?php
use GuzzleHttp\Client;

class HttpClient {
    private static $client = null;

    private static function init() {
        if (self::$client === null) {
            self::$client = new Client([
                'timeout'  => 5.0,
                'headers' => [
                    'User-Agent' => 'OmniShop/1.0',
                    'Accept'     => 'application/json',
                ]
            ]);
        }
    }

    public static function get($url, $options = []) {
        self::init();
        return self::$client->get($url, $options);
    }

    public static function post($url, $options = []) {
        self::init();
        return self::$client->post($url, $options);
    }
}
