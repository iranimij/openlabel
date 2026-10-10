<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Model\Css;

use Iranimij\OpenLabel\Api\Data\DesignInterface;
use Iranimij\OpenLabel\Api\Data\PlacementInterface;

/**
 * Turns a design (and a placement's offsets and gap) into one custom-property line for the per-store stylesheet
 * (10 · Front-end Review FE2). Mirrors the admin live preview's mapping so the shop looks like the preview.
 */
class DesignRules
{
    private const HEX = '/^#(?:[0-9a-f]{3}|[0-9a-f]{4}|[0-9a-f]{6}|[0-9a-f]{8})$/';
    private const DEFAULT_GAP = 4;

    /**
     * @param int $designId
     * @return string
     */
    public function designClass(int $designId): string
    {
        return 'ol-d-' . $designId;
    }

    /**
     * @param int $placementId
     * @return string
     */
    public function placementClass(int $placementId): string
    {
        return 'ol-p-' . $placementId;
    }

    /**
     * @param DesignInterface $design
     * @return string `.ol-d-<id>{...}` or an empty string for an unsaved design
     */
    public function forDesign(DesignInterface $design): string
    {
        if ($design->getDesignId() === null) {
            return '';
        }
        $props = [];
        foreach (['--ol-bg' => $design->getBgColor(), '--ol-fg' => $design->getTextColor(),
                     '--ol-border' => $design->getBorderColor()] as $name => $colour) {
            $colour = $this->colour($colour);
            if ($colour !== null) {
                $props[$name] = $colour;
            }
        }
        if ($design->getBorderWidth() > 0) {
            $props['--ol-bw'] = $design->getBorderWidth() . 'px';
        }
        $opacity = max(0, min(100, $design->getOpacity()));
        if ($opacity < 100) {
            $props['--ol-op'] = $this->number($opacity / 100);
        }
        if ($design->getRotation() !== 0) {
            $props['--ol-rot'] = $design->getRotation() . 'deg';
        }
        if ($design->getWidth() > 0) {
            $unit = $design->getSizeMode() === DesignInterface::SIZE_MODE_PX ? 'px' : 'cqw';
            $props['--ol-w'] = $design->getWidth() . $unit;
        }
        if ($design->getFontSize() > 0) {
            $props['--ol-fs-max'] = $design->getFontSize() . 'px';
        }

        return '.' . $this->designClass((int) $design->getDesignId()) . '{' . $this->join($props) . '}';
    }

    /**
     * @param PlacementInterface $placement
     * @return string|null `.ol-p-<id>{...}`, or null when the placement uses the structural defaults
     */
    public function forPlacement(PlacementInterface $placement): ?string
    {
        $props = [];
        if ($placement->getGap() !== self::DEFAULT_GAP) {
            $props['--ol-gap'] = max(0, $placement->getGap()) . 'px';
        }
        if ($placement->getOffsetX() !== 0) {
            $props['--ol-ox'] = $placement->getOffsetX() . 'px';
        }
        if ($placement->getOffsetY() !== 0) {
            $props['--ol-oy'] = $placement->getOffsetY() . 'px';
        }
        if ($props === [] || $placement->getPlacementId() === null) {
            return null;
        }

        return '.' . $this->placementClass((int) $placement->getPlacementId()) . '{' . $this->join($props) . '}';
    }

    /**
     * @param string|null $colour
     * @return string|null lower-case hex, or null for anything that is not a hex colour
     */
    private function colour(?string $colour): ?string
    {
        $colour = strtolower(trim((string) $colour));

        return preg_match(self::HEX, $colour) === 1 ? $colour : null;
    }

    /**
     * @param float $value
     * @return string
     */
    private function number(float $value): string
    {
        return rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.');
    }

    /**
     * @param array<string, string> $props
     * @return string
     */
    private function join(array $props): string
    {
        $parts = [];
        foreach ($props as $name => $value) {
            $parts[] = $name . ':' . $value;
        }

        return implode(';', $parts);
    }
}
