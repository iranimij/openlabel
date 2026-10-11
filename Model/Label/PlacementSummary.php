<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Model\Label;

/**
 * Turns "listing:tr,product:tl" (as aggregated by the grid collection) into "Listing TR, Product TL".
 */
class PlacementSummary
{
    /**
     * @param string|null $raw comma-separated area:position pairs in sort order
     * @return string
     */
    public function format(?string $raw): string
    {
        if ($raw === null || trim($raw) === '') {
            return (string) __('Nowhere yet');
        }
        $parts = [];
        foreach (explode(',', $raw) as $pair) {
            [$area, $position] = array_pad(explode(':', $pair, 2), 2, '');
            $parts[] = $this->areaLabel($area) . ' ' . strtoupper($position);
        }

        return implode(', ', $parts);
    }

    /**
     * @param string $area
     * @return string
     */
    private function areaLabel(string $area): string
    {
        return match ($area) {
            'listing' => (string) __('Listing'),
            'product' => (string) __('Product'),
            default => ucfirst($area),
        };
    }
}
