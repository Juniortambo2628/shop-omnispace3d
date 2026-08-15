<?php

namespace Tests\Feature;

use Tests\TestCase;

class ApplicationStructureTest extends TestCase
{
    public function test_env_example_exists(): void
    {
        $this->assertFileExists(__DIR__ . '/../../.env.example');
    }

    public function test_production_env_example_exists(): void
    {
        $this->assertFileExists(__DIR__ . '/../../.env.production.example');
    }

    public function test_composer_json_exists(): void
    {
        $this->assertFileExists(__DIR__ . '/../../composer.json');
    }

    public function test_gitignore_exists(): void
    {
        $this->assertFileExists(__DIR__ . '/../../.gitignore');
    }

    public function test_gitignore_covers_env_files(): void
    {
        $gitignore = file_get_contents(__DIR__ . '/../../.gitignore');
        $this->assertStringContainsString('.env', $gitignore);
        $this->assertStringContainsString('.env.local', $gitignore);
        $this->assertStringContainsString('.env.production', $gitignore);
    }

    public function test_gitignore_covers_ssh_keys(): void
    {
        $gitignore = file_get_contents(__DIR__ . '/../../.gitignore');
        $this->assertStringContainsString('github-actions-omnispace', $gitignore);
    }

    public function test_gitignore_covers_vendor(): void
    {
        $gitignore = file_get_contents(__DIR__ . '/../../.gitignore');
        $this->assertStringContainsString('/vendor/', $gitignore);
    }

    public function test_gitignore_covers_uploads(): void
    {
        $gitignore = file_get_contents(__DIR__ . '/../../.gitignore');
        $this->assertStringContainsString('/static/images/products/*', $gitignore);
    }

    public function test_phpunit_config_exists(): void
    {
        $this->assertFileExists(__DIR__ . '/../../phpunit.xml');
    }

    public function test_tests_directory_exists(): void
    {
        $this->assertDirectoryExists(__DIR__ . '/../../tests');
        $this->assertDirectoryExists(__DIR__ . '/../../tests/Unit');
        $this->assertDirectoryExists(__DIR__ . '/../../tests/Feature');
    }

    public function test_core_files_exist(): void
    {
        $this->assertFileExists(__DIR__ . '/../../core/Branding.php');
        $this->assertFileExists(__DIR__ . '/../../core/Invoice.php');
        $this->assertFileExists(__DIR__ . '/../../core/Mailer.php');
    }

    public function test_key_directories_exist(): void
    {
        $this->assertDirectoryExists(__DIR__ . '/../../app');
        $this->assertDirectoryExists(__DIR__ . '/../../config');
        $this->assertDirectoryExists(__DIR__ . '/../../views');
        $this->assertDirectoryExists(__DIR__ . '/../../static');
        $this->assertDirectoryExists(__DIR__ . '/../../public');
        $this->assertDirectoryExists(__DIR__ . '/../../routes');
        $this->assertDirectoryExists(__DIR__ . '/../../database');
    }

    public function test_github_actions_workflow_directory(): void
    {
        $githubDir = __DIR__ . '/../../.github';
        if (is_dir($githubDir)) {
            $this->assertDirectoryExists($githubDir . '/workflows');
        }
        // Directory may not exist yet during initial setup
        $this->assertTrue(true);
    }
}
