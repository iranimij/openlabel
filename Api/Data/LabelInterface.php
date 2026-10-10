<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Api\Data;

/**
 * A label: the "when and where". How it looks is a reusable Design.
 *
 * @api
 */
interface LabelInterface
{
    public const LABEL_ID = 'label_id';
    public const NAME = 'name';
    public const STATUS = 'status';
    public const PRIORITY = 'priority';
    public const STORE_IDS = 'store_ids';
    public const CUSTOMER_GROUP_IDS = 'customer_group_ids';
    public const VALID_FROM = 'valid_from';
    public const VALID_TO = 'valid_to';
    public const CONDITIONS_SERIALIZED = 'conditions_serialized';
    public const DESIGN_ID = 'design_id';
    public const APPLY_TO_PARENT = 'apply_to_parent';
    public const HIDE_LOWER_PRIORITY = 'hide_lower_priority';
    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = 'updated_at';
    public const PLACEMENTS = 'placements';

    public const STATUS_ENABLED = 1;
    public const STATUS_DISABLED = 0;

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
     * @return string
     */
    public function getName(): string;

    /**
     * @param string $name
     * @return $this
     */
    public function setName(string $name): self;

    /**
     * @return int STATUS_ENABLED or STATUS_DISABLED
     */
    public function getStatus(): int;

    /**
     * @param int $status
     * @return $this
     */
    public function setStatus(int $status): self;

    /**
     * @return int 0 = highest
     */
    public function getPriority(): int;

    /**
     * @param int $priority
     * @return $this
     */
    public function setPriority(int $priority): self;

    /**
     * @return int[] empty = all store views
     */
    public function getStoreIds(): array;

    /**
     * @param int[] $storeIds
     * @return $this
     */
    public function setStoreIds(array $storeIds): self;

    /**
     * @return int[] empty = all customer groups
     */
    public function getCustomerGroupIds(): array;

    /**
     * @param int[] $customerGroupIds
     * @return $this
     */
    public function setCustomerGroupIds(array $customerGroupIds): self;

    /**
     * @return string|null UTC datetime
     */
    public function getValidFrom(): ?string;

    /**
     * @param string|null $validFrom UTC datetime
     * @return $this
     */
    public function setValidFrom(?string $validFrom): self;

    /**
     * @return string|null UTC datetime
     */
    public function getValidTo(): ?string;

    /**
     * @param string|null $validTo UTC datetime
     * @return $this
     */
    public function setValidTo(?string $validTo): self;

    /**
     * @return string|null JSON in the core Magento\Rule combine format
     */
    public function getConditionsSerialized(): ?string;

    /**
     * @param string|null $conditions
     * @return $this
     */
    public function setConditionsSerialized(?string $conditions): self;

    /**
     * @return int|null
     */
    public function getDesignId(): ?int;

    /**
     * @param int $designId
     * @return $this
     */
    public function setDesignId(int $designId): self;

    /**
     * @return bool
     */
    public function isApplyToParent(): bool;

    /**
     * @param bool $applyToParent
     * @return $this
     */
    public function setApplyToParent(bool $applyToParent): self;

    /**
     * @return bool
     */
    public function isHideLowerPriority(): bool;

    /**
     * @param bool $hideLowerPriority
     * @return $this
     */
    public function setHideLowerPriority(bool $hideLowerPriority): self;

    /**
     * @return string|null
     */
    public function getCreatedAt(): ?string;

    /**
     * @return string|null
     */
    public function getUpdatedAt(): ?string;

    /**
     * @return \Iranimij\OpenLabel\Api\Data\PlacementInterface[]
     */
    public function getPlacements(): array;

    /**
     * @param \Iranimij\OpenLabel\Api\Data\PlacementInterface[] $placements
     * @return $this
     */
    public function setPlacements(array $placements): self;
}
