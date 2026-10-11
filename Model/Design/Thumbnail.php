<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Model\Design;

use Magento\Framework\Escaper;

/**
 * Small admin thumbnail of a design (grids, design picker). Inline SVG with presentation attributes only,
 * so it needs no inline styles. Image designs show the uploaded image.
 */
class Thumbnail
{
    private const NEUTRAL_BG = '#6b7280';
    private const NEUTRAL_FG = '#ffffff';
    private const MAX_CHARS = 12;

    /**
     * @param Escaper $escaper
     */
    public function __construct(
        private readonly Escaper $escaper
    ) {
    }

    /**
     * @param array<string, mixed> $design keys: type, shape, bg_color, text_color, border_color, text, image_url,
     *                                     alt_text
     * @return string HTML
     */
    public function render(array $design): string
    {
        if (($design['type'] ?? '') === 'image') {
            return sprintf(
                '<img class="ol-admin-thumb" src="%s" alt="%s" width="64" height="24" loading="lazy"/>',
                $this->escaper->escapeHtml($this->url((string) ($design['image_url'] ?? ''))),
                $this->escaper->escapeHtml((string) ($design['alt_text'] ?? ''))
            );
        }
        $bg = $this->colour($design['bg_color'] ?? null, self::NEUTRAL_BG);
        $fg = $this->colour($design['text_color'] ?? null, self::NEUTRAL_FG);
        $border = $this->colour($design['border_color'] ?? null, $bg);
        $text = $this->escaper->escapeHtml($this->shorten((string) ($design['text'] ?? '')));
        $shape = ($design['type'] ?? '') === 'shape' ? (string) ($design['shape'] ?? 'rectangle') : 'rectangle';

        if ($shape === 'circle') {
            return sprintf(
                '<svg class="ol-admin-thumb" width="28" height="28" viewBox="0 0 28 28" role="img" aria-hidden="true">'
                . '<circle cx="14" cy="14" r="13" fill="%s" stroke="%s" stroke-width="1"/>'
                . '<text x="14" y="18" font-size="9" text-anchor="middle" fill="%s">%s</text></svg>',
                $bg,
                $border,
                $fg,
                $text
            );
        }
        $radius = match ($shape) {
            'pill' => 11,
            'rectangle' => 3,
            default => 0,
        };

        return sprintf(
            '<svg class="ol-admin-thumb" width="88" height="22" viewBox="0 0 88 22" role="img" aria-hidden="true">'
            . '<rect x="0.5" y="0.5" width="87" height="21" rx="%d" fill="%s" stroke="%s" stroke-width="1"/>'
            . '<text x="44" y="15" font-size="11" text-anchor="middle" fill="%s">%s</text></svg>',
            $radius,
            $bg,
            $border,
            $fg,
            $text
        );
    }

    /**
     * @param mixed $value
     * @param string $fallback
     * @return string
     */
    private function colour(mixed $value, string $fallback): string
    {
        return is_string($value) && preg_match('/^#[0-9a-fA-F]{3,8}$/', $value) ? $value : $fallback;
    }

    /**
     * Only http(s) and site-relative image URLs are rendered.
     *
     * @param string $url
     * @return string
     */
    private function url(string $url): string
    {
        return preg_match('#^(https?://|/)#i', $url) ? $url : '';
    }

    /**
     * @param string $text
     * @return string
     */
    private function shorten(string $text): string
    {
        $text = trim(strip_tags($text));

        return mb_strlen($text) > self::MAX_CHARS ? mb_substr($text, 0, self::MAX_CHARS - 1) . '…' : $text;
    }
}
