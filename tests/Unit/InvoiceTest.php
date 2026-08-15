<?php

namespace Tests\Unit;

use Tests\TestCase;

class InvoiceTest extends TestCase
{
    private string $invoicePath;

    protected function setUp(): void
    {
        parent::setUp();
        $this->invoicePath = __DIR__ . '/../../core/Invoice.php';
    }

    public function test_invoice_uses_branding_class(): void
    {
        $code = file_get_contents($this->invoicePath);
        $this->assertStringContainsString('Branding::TEAL', $code, 'Invoice should use Branding::TEAL');
        $this->assertStringContainsString('Branding::TABLE_ALT', $code, 'Invoice should use Branding::TABLE_ALT');
        $this->assertStringContainsString('Branding::GREY', $code, 'Invoice should use Branding::GREY');
        $this->assertStringContainsString('Branding::DARK', $code, 'Invoice should use Branding::DARK');
    }

    public function test_invoice_no_hardcoded_teal(): void
    {
        $code = file_get_contents($this->invoicePath);
        // Check that the buildHtml method doesn't have hardcoded #0A9696
        // (except in comments or strings that reference the Branding class)
        $lines = explode("\n", $code);
        foreach ($lines as $line) {
            if (str_contains($line, 'Branding::') || str_contains($line, '//')) {
                continue;
            }
            $this->assertStringNotContainsString('#0A9696', $line, 
                "Line should not contain hardcoded #0A9696: " . trim($line));
        }
    }

    public function test_invoice_uses_branding_logo(): void
    {
        $code = file_get_contents($this->invoicePath);
        $this->assertStringContainsString('Branding::logoDataUri()', $code, 
            'Invoice should use Branding::logoDataUri()');
    }
}
