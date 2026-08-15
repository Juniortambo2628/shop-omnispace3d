<?php

namespace Tests\Unit;

use Tests\TestCase;

class MailerTest extends TestCase
{
    private string $mailerPath;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mailerPath = __DIR__ . '/../../core/Mailer.php';
    }

    public function test_mailer_uses_branding_class(): void
    {
        $code = file_get_contents($this->mailerPath);
        $this->assertStringContainsString('Branding::TEAL', $code, 'Mailer should use Branding::TEAL');
        $this->assertStringContainsString('Branding::LT_TEAL', $code, 'Mailer should use Branding::LT_TEAL');
        $this->assertStringContainsString('Branding::PALE', $code, 'Mailer should use Branding::PALE');
        $this->assertStringContainsString('Branding::CHARCOAL', $code, 'Mailer should use Branding::CHARCOAL');
        $this->assertStringContainsString('Branding::GREY', $code, 'Mailer should use Branding::GREY');
    }

    public function test_mailer_no_hardcoded_colors(): void
    {
        $code = file_get_contents($this->mailerPath);
        $lines = explode("\n", $code);
        foreach ($lines as $line) {
            if (str_contains($line, 'Branding::') || str_contains($line, '//')) {
                continue;
            }
            $this->assertStringNotContainsString('#0A9696', $line, 
                "Line should not contain hardcoded #0A9696: " . trim($line));
            $this->assertStringNotContainsString('#19AFAC', $line, 
                "Line should not contain hardcoded #19AFAC: " . trim($line));
            $this->assertStringNotContainsString('#D6F0EF', $line, 
                "Line should not contain hardcoded #D6F0EF: " . trim($line));
        }
    }

    public function test_mailer_uses_branding_logo(): void
    {
        $code = file_get_contents($this->mailerPath);
        $this->assertStringContainsString('Branding::emailLogoPath()', $code, 
            'Mailer should use Branding::emailLogoPath()');
    }
}
