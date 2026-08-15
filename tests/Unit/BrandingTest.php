<?php

namespace Tests\Unit;

use Tests\TestCase;
use Branding;

class BrandingTest extends TestCase
{
    public function test_brand_colors_are_defined(): void
    {
        $this->assertSame('#0A9696', Branding::TEAL);
        $this->assertSame('#19AFAC', Branding::LT_TEAL);
        $this->assertSame('#D6F0EF', Branding::PALE);
        $this->assertSame('#333333', Branding::CHARCOAL);
        $this->assertSame('#6E6E6E', Branding::GREY);
        $this->assertSame('#FFFFFF', Branding::WHITE);
    }

    public function test_typography_constants_are_set(): void
    {
        $this->assertNotEmpty(Branding::HEADING_FONT);
        $this->assertNotEmpty(Branding::BODY_FONT);
        $this->assertSame('Your Space, Your Way', Branding::TAGLINE);
    }

    public function test_logo_paths_are_defined(): void
    {
        $this->assertNotEmpty(Branding::LOGO_WHITE_BG);
        $this->assertNotEmpty(Branding::LOGO_TRANSPARENT_BG);
        $this->assertNotEmpty(Branding::LOGO_INVOICE);
    }

    public function test_css_variables_returns_string(): void
    {
        $css = Branding::cssVariables();
        $this->assertIsString($css);
        $this->assertStringContainsString('--brand-teal:', $css);
        $this->assertStringContainsString(Branding::TEAL, $css);
    }

    public function test_extended_colors_are_defined(): void
    {
        $this->assertNotEmpty(Branding::TEAL_DARK);
        $this->assertNotEmpty(Branding::TEAL_LIGHT);
        $this->assertNotEmpty(Branding::BORDER_GREY);
        $this->assertNotEmpty(Branding::DARK);
        $this->assertNotEmpty(Branding::LIGHT_GREY);
        $this->assertNotEmpty(Branding::TABLE_ALT);
    }
}
