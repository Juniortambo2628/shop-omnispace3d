<?php

/**
 * Branding — Single source of truth for all brand colors, fonts, and logo paths.
 *
 * Every PHP file that needs brand values should reference this class.
 * CSS values are derived from tokens.css (which should mirror these constants).
 */
class Branding
{
    // ── Brand Colors ──
    public const TEAL        = '#0A9696';  // PRIMARY — Headings, borders, section banners, logo colour
    public const LT_TEAL     = '#19AFAC';  // ACCENT — Table headers, accent panels, card borders
    public const PALE        = '#D6F0EF';  // LIGHT FILL — Alternating rows, light backgrounds
    public const CHARCOAL    = '#333333';  // BODY TEXT — Main body copy
    public const GREY        = '#6E6E6E';  // SUBHEADINGS — Subheadings, captions, supporting text
    public const WHITE       = '#FFFFFF';  // BACKGROUND — Page backgrounds, text on teal

    // ── Extended / Derived ──
    public const TEAL_DARK   = '#088080';
    public const TEAL_LIGHT  = '#f9fffe';
    public const BORDER_GREY = '#e0e0e0';
    public const DARK        = '#1a1a1a';
    public const LIGHT_GREY  = '#999999';
    public const TABLE_ALT   = '#f1f8f8';

    // ── Typography ──
    public const HEADING_FONT = 'Arial, Helvetica, sans-serif';
    public const BODY_FONT    = 'Arial, Helvetica, sans-serif';
    public const TAGLINE      = 'Your Space, Your Way';

    // ── Logo Paths ──
    // Standard use on white or light backgrounds
    public const LOGO_WHITE_BG        = '/static/images/omnispace-logo.jpg';
    // For teal or dark cover/section slides (transparent background)
    public const LOGO_TRANSPARENT_BG  = '/static/images/omnispace-logo-white.png';
    // Invoice-specific logo (white bg, optimized for PDF)
    public const LOGO_INVOICE         = '/static/images/omnispace-invoice-logo.jpg';

    // ── CSS Variable Mapping ──
    public static function cssVariables(): string
    {
        return implode('; ', [
            '--brand-teal: ' . self::TEAL,
            '--brand-teal-dark: ' . self::TEAL_DARK,
            '--brand-teal-pale: ' . self::PALE,
            '--brand-teal-light: ' . self::TEAL_LIGHT,
            '--color-text: ' . self::CHARCOAL,
            '--color-text-secondary: ' . self::GREY,
            '--font-family: ' . self::BODY_FONT,
        ]);
    }

    // ── Helper: get logo data URI for PDF embedding ──
    public static function logoDataUri(): string
    {
        $candidates = [
            STATIC_PATH . '/images/omnispace-logo.jpg',
            STATIC_PATH . '/images/omnispace-invoice-logo.jpg',
        ];

        foreach ($candidates as $path) {
            if (! is_readable($path)) {
                continue;
            }

            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            $mime = match ($ext) {
                'png' => 'image/png',
                'webp' => 'image/webp',
                'gif' => 'image/gif',
                default => 'image/jpeg',
            };

            return 'data:' . $mime . ';base64,' . base64_encode((string) file_get_contents($path));
        }

        return '';
    }

    // ── Helper: get email logo path ──
    public static function emailLogoPath(): ?string
    {
        $candidates = [
            STATIC_PATH . '/images/omnispace-logo-white.png',
            STATIC_PATH . '/images/omnispace-logo.jpg',
        ];

        foreach ($candidates as $path) {
            if (is_readable($path)) {
                return $path;
            }
        }

        return null;
    }
}
