<?php

namespace App\Support;

use App\Models\SchoolSetting;

/**
 * The school's brand colour for places that cannot use CSS variables (PDFs, emails).
 * Always returns a valid, readable-on-white #rrggbb value.
 */
class Theme
{
    public const DEFAULT = '#0f766e';

    /** Sidebar colour from Settings, darkened if it is too pale to read on white. */
    public static function primary(): string
    {
        static $cached = null;
        if ($cached === null) {
            try {
                $cached = self::readable(SchoolSetting::current()?->sidebar_color);
            } catch (\Throwable $e) {
                $cached = self::DEFAULT;
            }
        }
        return $cached;
    }

    public static function readable(?string $hex): string
    {
        $hex = self::normalise($hex);
        if (self::luminance($hex) > 0.5) {
            $hex = self::mix($hex, '#000000', 0.5);
        }
        return $hex;
    }

    /** A pale version of the brand colour: $amount = how much white is mixed in (0..1). */
    public static function tint(?string $hex = null, float $amount = 0.9): string
    {
        $base = $hex ? self::normalise($hex) : self::primary();
        return self::mix($base, '#ffffff', $amount);
    }

    private static function normalise(?string $hex): string
    {
        $hex = trim((string) $hex);
        if (preg_match('/^#[0-9a-fA-F]{6}$/', $hex)) {
            return strtolower($hex);
        }
        if (preg_match('/^#([0-9a-fA-F])([0-9a-fA-F])([0-9a-fA-F])$/', $hex, $m)) {
            return strtolower('#' . $m[1] . $m[1] . $m[2] . $m[2] . $m[3] . $m[3]);
        }
        return self::DEFAULT;
    }

    private static function rgb(string $hex): array
    {
        return [hexdec(substr($hex, 1, 2)), hexdec(substr($hex, 3, 2)), hexdec(substr($hex, 5, 2))];
    }

    private static function luminance(string $hex): float
    {
        [$r, $g, $b] = self::rgb($hex);
        return (0.2126 * $r + 0.7152 * $g + 0.0722 * $b) / 255;
    }

    /** Mix $a with $b; $w is the weight of $b. */
    private static function mix(string $a, string $b, float $w): string
    {
        $x = self::rgb($a);
        $y = self::rgb($b);
        return sprintf('#%02x%02x%02x',
            round($x[0] * (1 - $w) + $y[0] * $w),
            round($x[1] * (1 - $w) + $y[1] * $w),
            round($x[2] * (1 - $w) + $y[2] * $w));
    }
}
