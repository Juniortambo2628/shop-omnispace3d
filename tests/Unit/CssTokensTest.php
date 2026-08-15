<?php

namespace Tests\Unit;

use Tests\TestCase;

class CssTokensTest extends TestCase
{
    private string $tokensPath;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tokensPath = __DIR__ . '/../../static/css/tokens.css';
    }

    public function test_tokens_file_exists(): void
    {
        $this->assertFileExists($this->tokensPath);
    }

    public function test_brand_colors_in_tokens_match_branding(): void
    {
        $css = file_get_contents($this->tokensPath);

        // Verify brand colors are present in CSS
        $this->assertStringContainsString('#0A9696', $css, 'Brand Teal missing from tokens.css');
        $this->assertStringContainsString('#19AFAC', $css, 'Light Teal missing from tokens.css');
        $this->assertStringContainsString('#D6F0EF', $css, 'Pale Teal missing from tokens.css');
        $this->assertStringContainsString('#333333', $css, 'Charcoal missing from tokens.css');
        $this->assertStringContainsString('#6E6E6E', $css, 'Mid Grey missing from tokens.css');
    }

    public function test_font_is_arial(): void
    {
        $css = file_get_contents($this->tokensPath);
        $this->assertStringContainsString('Arial', $css, 'Arial font should be in tokens.css');
    }

    public function test_no_montserrat_font(): void
    {
        $css = file_get_contents($this->tokensPath);
        $this->assertStringNotContainsString('Montserrat', $css, 'Montserrat should not be in tokens.css');
    }
}
