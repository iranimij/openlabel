<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Model\Design;

/**
 * WCAG 2.x contrast ratio between two hex colours (relative luminance formula). The admin badge uses the same
 * formula client-side; this class backs the server-side checks and the system-design guarantee (FE9).
 */
class Contrast
{
    public const LEVEL_PASS = 'pass';
    public const LEVEL_LARGE = 'large';
    public const LEVEL_FAIL = 'fail';
    public const LEVEL_UNKNOWN = 'unknown';

    public const MIN_NORMAL = 4.5;
    public const MIN_LARGE = 3.0;

    /**
     * Contrast ratio from 1 to 21, or null when a colour is not a hex value. Alpha channels are ignored.
     *
     * @param string|null $foreground
     * @param string|null $background
     * @return float|null
     */
    public function ratio(?string $foreground, ?string $background): ?float
    {
        $fg = $this->luminance($foreground);
        $bg = $this->luminance($background);
        if ($fg === null || $bg === null) {
            return null;
        }

        return (max($fg, $bg) + 0.05) / (min($fg, $bg) + 0.05);
    }

    /**
     * Badge level: pass (≥ 4.5), large (≥ 3, large text only), fail, or unknown.
     *
     * @param string|null $foreground
     * @param string|null $background
     * @return string
     */
    public function level(?string $foreground, ?string $background): string
    {
        $ratio = $this->ratio($foreground, $background);
        if ($ratio === null) {
            return self::LEVEL_UNKNOWN;
        }
        if ($ratio >= self::MIN_NORMAL) {
            return self::LEVEL_PASS;
        }

        return $ratio >= self::MIN_LARGE ? self::LEVEL_LARGE : self::LEVEL_FAIL;
    }

    /**
     * @param string|null $hex
     * @return float|null
     */
    private function luminance(?string $hex): ?float
    {
        if ($hex === null || !preg_match('/^#([0-9a-f]{3}|[0-9a-f]{6}|[0-9a-f]{8})$/i', $hex, $match)) {
            return null;
        }
        $digits = $match[1];
        if (strlen($digits) === 3) {
            $digits = $digits[0] . $digits[0] . $digits[1] . $digits[1] . $digits[2] . $digits[2];
        }
        $channels = [];
        foreach ([0, 2, 4] as $offset) {
            $value = hexdec(substr($digits, $offset, 2)) / 255;
            $channels[] = $value <= 0.03928 ? $value / 12.92 : (($value + 0.055) / 1.055) ** 2.4;
        }

        return 0.2126 * $channels[0] + 0.7152 * $channels[1] + 0.0722 * $channels[2];
    }
}
