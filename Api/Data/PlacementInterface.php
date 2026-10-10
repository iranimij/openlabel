<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Api\Data;

/**
 * One "where" of a label: an area, a position on the product image and stacking rules.
 *
 * @api
 */
interface PlacementInterface
{
    public const PLACEMENT_ID = 'placement_id';
    public const LABEL_ID = 'label_id';
    public const AREA = 'area';
    public const POSITION = 'position';
    public const PIN_PHYSICAL_SIDE = 'pin_physical_side';
    public const DESIGN_ID = 'design_id';
    public const OFFSET_X = 'offset_x';
    public const OFFSET_Y = 'offset_y';
    public const MAX_LABELS = 'max_labels';
    public const STACKING = 'stacking';
    public const GAP = 'gap';
    public const SORT_ORDER = 'sort_order';

    public const AREA_LISTING = 'listing';
    public const AREA_PRODUCT = 'product';
    /** Areas available in this version. */
    public const AREAS = [self::AREA_LISTING, self::AREA_PRODUCT];

    /** Admin positions; rendered as logical (RTL-aware) classes. */
    public const POSITIONS = ['tl', 'tc', 'tr', 'ml', 'mc', 'mr', 'bl', 'bc', 'br'];

    public const STACKING_VERTICAL = 'vertical';
    public const STACKING_HORIZONTAL = 'horizontal';

    /**
     * @return int|null
     */
    public function getPlacementId(): ?int;

    /**
     * @param int $placementId
     * @return $this
     */
    public function setPlacementId(int $placementId): self;

    /**
     * @return int|null
     */
    public function getLabelId(): ?int;

    /**
     * @param int $labelId
     * @return $this
     */
    public function setLabelId(int $labelId): self;

    /**
     * @return string listing|product
     */
    public function getArea(): string;

    /**
     * @param string $area
     * @return $this
     */
    public function setArea(string $area): self;

    /**
     * @return string one of POSITIONS
     */
    public function getPosition(): string;

    /**
     * @param string $position
     * @return $this
     */
    public function setPosition(string $position): self;

    /**
     * @return bool
     */
    public function isPinPhysicalSide(): bool;

    /**
     * @param bool $pin
     * @return $this
     */
    public function setPinPhysicalSide(bool $pin): self;

    /**
     * Design override for this area, null = the label's design.
     *
     * @return int|null
     */
    public function getDesignId(): ?int;

    /**
     * @param int|null $designId
     * @return $this
     */
    public function setDesignId(?int $designId): self;

    /**
     * @return int px
     */
    public function getOffsetX(): int;

    /**
     * @param int $offsetX
     * @return $this
     */
    public function setOffsetX(int $offsetX): self;

    /**
     * @return int px
     */
    public function getOffsetY(): int;

    /**
     * @param int $offsetY
     * @return $this
     */
    public function setOffsetY(int $offsetY): self;

    /**
     * @return int
     */
    public function getMaxLabels(): int;

    /**
     * @param int $maxLabels
     * @return $this
     */
    public function setMaxLabels(int $maxLabels): self;

    /**
     * @return string vertical|horizontal
     */
    public function getStacking(): string;

    /**
     * @param string $stacking
     * @return $this
     */
    public function setStacking(string $stacking): self;

    /**
     * @return int px
     */
    public function getGap(): int;

    /**
     * @param int $gap
     * @return $this
     */
    public function setGap(int $gap): self;

    /**
     * @return int
     */
    public function getSortOrder(): int;

    /**
     * @param int $sortOrder
     * @return $this
     */
    public function setSortOrder(int $sortOrder): self;
}
