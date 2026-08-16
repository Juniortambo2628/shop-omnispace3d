<?php

namespace Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Load .env for tests if it exists
        if (file_exists(__DIR__ . '/../.env')) {
            $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
            $dotenv->safeLoad();
        }

        // Load core classes (non-namespaced, not in Composer autoload)
        require_once __DIR__ . '/../core/Branding.php';
    }
}
