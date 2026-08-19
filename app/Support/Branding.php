<?php

namespace App\Support;

use App\Models\SiteSetting;

class Branding
{
    public static function fonts(): array
    {
        return config('branding.fonts', []);
    }

    public static function defaultFontKey(): string
    {
        return (string) config('branding.default_font', 'cairo');
    }

    public static function fontKey(): string
    {
        $key = (string) SiteSetting::get('font_family', self::defaultFontKey());
        $fonts = self::fonts();

        return array_key_exists($key, $fonts) ? $key : self::defaultFontKey();
    }

    public static function font(?string $key = null): array
    {
        $fonts = self::fonts();
        $resolved = $key ?: self::fontKey();

        return $fonts[$resolved] ?? $fonts[self::defaultFontKey()] ?? [
            'label' => 'Cairo',
            'label_ar' => 'القاهرة',
            'description' => '',
            'family' => "'Cairo', Tahoma, sans-serif",
            'bunny' => 'cairo:400,500,700',
        ];
    }

    public static function primaryColor(): string
    {
        $color = SiteSetting::get('primary_color', '#6366f1');

        return self::normalizeHex(is_string($color) ? $color : null, '#6366f1');
    }

    public static function secondaryColor(): string
    {
        $color = SiteSetting::get('secondary_color', '#10b981');

        return self::normalizeHex(is_string($color) ? $color : null, '#10b981');
    }

    /**
     * Derived public-site palette so one chosen primary stays harmonious across UI.
     *
     * @return array<string, string>
     */
    public static function palette(?string $primary = null, ?string $secondary = null): array
    {
        $primary = self::normalizeHex($primary, self::primaryColor());
        $secondary = self::normalizeHex($secondary, self::secondaryColor());

        [$h, $s, $l] = self::hexToHsl($primary);

        // Accent: shift hue ~28° for gradients (indigo→pink vibe, but relative to chosen color)
        $accent = self::hslToHex(
            fmod($h + 28 + 360, 360),
            min(100, $s + 8),
            self::clamp($l + 4, 28, 62)
        );

        // Cool companion for dual-tone CTAs (indigo→blue style)
        $companion = self::hslToHex(
            fmod($h - 18 + 360, 360),
            min(100, $s + 4),
            self::clamp($l + 2, 30, 58)
        );

        $primaryHover = self::hslToHex($h, $s, self::clamp($l - 10, 12, 48));
        $accentHover = self::mix($accent, '#000000', 0.18);
        $companionHover = self::mix($companion, '#000000', 0.18);

        $primarySoft = self::mix($primary, '#ffffff', 0.88);
        $primaryMuted = self::mix($primary, '#ffffff', 0.78);
        $primaryTint = self::mix($primary, '#ffffff', 0.92);
        $primaryDeep = self::hslToHex($h, min(100, $s + 10), self::clamp($l - 28, 8, 28));

        $surfaceFrom = self::hslToHex($h, min(100, $s + 5), self::clamp($l + 8, 40, 62));
        $surfaceTo = self::hslToHex(
            fmod($h + 22 + 360, 360),
            min(100, $s + 6),
            self::clamp($l - 6, 28, 52)
        );

        return [
            'primary' => $primary,
            'primary_hover' => $primaryHover,
            'primary_soft' => $primarySoft,
            'primary_muted' => $primaryMuted,
            'primary_tint' => $primaryTint,
            'primary_deep' => $primaryDeep,
            'accent' => $accent,
            'accent_hover' => $accentHover,
            'companion' => $companion,
            'companion_hover' => $companionHover,
            'gradient_from' => $primary,
            'gradient_to' => $companion,
            'gradient_cta_from' => $primary,
            'gradient_cta_to' => $accent,
            'gradient_from_hover' => $primaryHover,
            'gradient_to_hover' => $companionHover,
            'gradient_cta_from_hover' => $primaryHover,
            'gradient_cta_to_hover' => $accentHover,
            'surface_from' => $surfaceFrom,
            'surface_to' => $surfaceTo,
            'secondary' => $secondary,
            'secondary_hover' => self::mix($secondary, '#000000', 0.18),
            'secondary_soft' => self::mix($secondary, '#ffffff', 0.86),
        ];
    }

    public static function normalizeHex(?string $color, string $fallback): string
    {
        if (! is_string($color)) {
            return $fallback;
        }

        $color = trim($color);
        if (preg_match('/^#[0-9A-Fa-f]{6}$/', $color)) {
            return strtolower($color);
        }

        if (preg_match('/^#[0-9A-Fa-f]{3}$/', $color)) {
            return strtolower(sprintf(
                '#%s%s%s%s%s%s',
                $color[1],
                $color[1],
                $color[2],
                $color[2],
                $color[3],
                $color[3]
            ));
        }

        return $fallback;
    }

    /**
     * @return array{0: float, 1: float, 2: float} H (0-360), S (0-100), L (0-100)
     */
    public static function hexToHsl(string $hex): array
    {
        $hex = ltrim(self::normalizeHex($hex, '#6366f1'), '#');
        $r = hexdec(substr($hex, 0, 2)) / 255;
        $g = hexdec(substr($hex, 2, 2)) / 255;
        $b = hexdec(substr($hex, 4, 2)) / 255;

        $max = max($r, $g, $b);
        $min = min($r, $g, $b);
        $l = ($max + $min) / 2;
        $d = $max - $min;

        if ($d < 0.00001) {
            return [0.0, 0.0, $l * 100];
        }

        $s = $l > 0.5 ? $d / (2 - $max - $min) : $d / ($max + $min);

        switch ($max) {
            case $r:
                $h = (($g - $b) / $d) + ($g < $b ? 6 : 0);
                break;
            case $g:
                $h = (($b - $r) / $d) + 2;
                break;
            default:
                $h = (($r - $g) / $d) + 4;
                break;
        }

        return [$h * 60, $s * 100, $l * 100];
    }

    public static function hslToHex(float $h, float $s, float $l): string
    {
        $h = fmod($h + 360, 360);
        $s = self::clamp($s, 0, 100) / 100;
        $l = self::clamp($l, 0, 100) / 100;

        if ($s < 0.00001) {
            $v = (int) round($l * 255);

            return sprintf('#%02x%02x%02x', $v, $v, $v);
        }

        $q = $l < 0.5 ? $l * (1 + $s) : $l + $s - $l * $s;
        $p = 2 * $l - $q;
        $hk = $h / 360;

        $r = self::hueToRgb($p, $q, $hk + 1 / 3);
        $g = self::hueToRgb($p, $q, $hk);
        $b = self::hueToRgb($p, $q, $hk - 1 / 3);

        return sprintf(
            '#%02x%02x%02x',
            (int) round($r * 255),
            (int) round($g * 255),
            (int) round($b * 255)
        );
    }

    /**
     * Mix two hex colors. $amount is weight of $with (0..1).
     */
    public static function mix(string $base, string $with, float $amount): string
    {
        $amount = self::clamp($amount, 0, 1);
        $base = ltrim(self::normalizeHex($base, '#000000'), '#');
        $with = ltrim(self::normalizeHex($with, '#ffffff'), '#');

        $channels = static function (string $hex): array {
            return [
                hexdec(substr($hex, 0, 2)),
                hexdec(substr($hex, 2, 2)),
                hexdec(substr($hex, 4, 2)),
            ];
        };

        [$br, $bg, $bb] = $channels($base);
        [$wr, $wg, $wb] = $channels($with);

        return sprintf(
            '#%02x%02x%02x',
            (int) round($br + ($wr - $br) * $amount),
            (int) round($bg + ($wg - $bg) * $amount),
            (int) round($bb + ($wb - $bb) * $amount)
        );
    }

    private static function hueToRgb(float $p, float $q, float $t): float
    {
        if ($t < 0) {
            $t += 1;
        }
        if ($t > 1) {
            $t -= 1;
        }
        if ($t < 1 / 6) {
            return $p + ($q - $p) * 6 * $t;
        }
        if ($t < 1 / 2) {
            return $q;
        }
        if ($t < 2 / 3) {
            return $p + ($q - $p) * (2 / 3 - $t) * 6;
        }

        return $p;
    }

    private static function clamp(float $value, float $min, float $max): float
    {
        return max($min, min($max, $value));
    }
}
