<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Model;

use Iranimij\OpenLabel\Api\Data\PlacementInterface;
use Iranimij\OpenLabel\Model\ResourceModel\Placement as PlacementResource;
use Magento\Framework\Model\AbstractModel;

class Placement extends AbstractModel implements PlacementInterface
{
    /**
     * @inheritDoc
     */
    protected function _construct(): void
    {
        $this->_init(PlacementResource::class);
    }

    /**
     * @inheritDoc
     */
    public function getPlacementId(): ?int
    {
        return $this->getData(self::PLACEMENT_ID) === null ? null : (int) $this->getData(self::PLACEMENT_ID);
    }

    /**
     * @inheritDoc
     */
    public function setPlacementId(int $placementId): PlacementInterface
    {
        return $this->setData(self::PLACEMENT_ID, $placementId);
    }

    /**
     * @inheritDoc
     */
    public function getLabelId(): ?int
    {
        return $this->getData(self::LABEL_ID) === null ? null : (int) $this->getData(self::LABEL_ID);
    }

    /**
     * @inheritDoc
     */
    public function setLabelId(int $labelId): PlacementInterface
    {
        return $this->setData(self::LABEL_ID, $labelId);
    }

    /**
     * @inheritDoc
     */
    public function getArea(): string
    {
        return (string) ($this->getData(self::AREA) ?? self::AREA_LISTING);
    }

    /**
     * @inheritDoc
     */
    public function setArea(string $area): PlacementInterface
    {
        return $this->setData(self::AREA, $area);
    }

    /**
     * @inheritDoc
     */
    public function getPosition(): string
    {
        return (string) ($this->getData(self::POSITION) ?? 'tl');
    }

    /**
     * @inheritDoc
     */
    public function setPosition(string $position): PlacementInterface
    {
        return $this->setData(self::POSITION, $position);
    }

    /**
     * @inheritDoc
     */
    public function isPinPhysicalSide(): bool
    {
        return (bool) $this->getData(self::PIN_PHYSICAL_SIDE);
    }

    /**
     * @inheritDoc
     */
    public function setPinPhysicalSide(bool $pin): PlacementInterface
    {
        return $this->setData(self::PIN_PHYSICAL_SIDE, $pin);
    }

    /**
     * @inheritDoc
     */
    public function getDesignId(): ?int
    {
        $id = $this->getData(self::DESIGN_ID);

        return $id === null || $id === '' ? null : (int) $id;
    }

    /**
     * @inheritDoc
     */
    public function setDesignId(?int $designId): PlacementInterface
    {
        return $this->setData(self::DESIGN_ID, $designId);
    }

    /**
     * @inheritDoc
     */
    public function getOffsetX(): int
    {
        return (int) $this->getData(self::OFFSET_X);
    }

    /**
     * @inheritDoc
     */
    public function setOffsetX(int $offsetX): PlacementInterface
    {
        return $this->setData(self::OFFSET_X, $offsetX);
    }

    /**
     * @inheritDoc
     */
    public function getOffsetY(): int
    {
        return (int) $this->getData(self::OFFSET_Y);
    }

    /**
     * @inheritDoc
     */
    public function setOffsetY(int $offsetY): PlacementInterface
    {
        return $this->setData(self::OFFSET_Y, $offsetY);
    }

    /**
     * @inheritDoc
     */
    public function getMaxLabels(): int
    {
        return $this->getData(self::MAX_LABELS) === null ? 3 : (int) $this->getData(self::MAX_LABELS);
    }

    /**
     * @inheritDoc
     */
    public function setMaxLabels(int $maxLabels): PlacementInterface
    {
        return $this->setData(self::MAX_LABELS, $maxLabels);
    }

    /**
     * @inheritDoc
     */
    public function getStacking(): string
    {
        return (string) ($this->getData(self::STACKING) ?? self::STACKING_VERTICAL);
    }

    /**
     * @inheritDoc
     */
    public function setStacking(string $stacking): PlacementInterface
    {
        return $this->setData(self::STACKING, $stacking);
    }

    /**
     * @inheritDoc
     */
    public function getGap(): int
    {
        return $this->getData(self::GAP) === null ? 4 : (int) $this->getData(self::GAP);
    }

    /**
     * @inheritDoc
     */
    public function setGap(int $gap): PlacementInterface
    {
        return $this->setData(self::GAP, $gap);
    }

    /**
     * @inheritDoc
     */
    public function getSortOrder(): int
    {
        return (int) $this->getData(self::SORT_ORDER);
    }

    /**
     * @inheritDoc
     */
    public function setSortOrder(int $sortOrder): PlacementInterface
    {
        return $this->setData(self::SORT_ORDER, $sortOrder);
    }
}
