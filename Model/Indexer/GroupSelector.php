<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Model\Indexer;

use Iranimij\OpenLabel\Api\Data\LabelInterface;
use Magento\Customer\Model\ResourceModel\Group\CollectionFactory;

/**
 * Which customer_group_id values a label writes: -1 (all groups) unless a condition reads the price index,
 * in which case one row per group the label is limited to, or per existing group (06 · F5).
 */
class GroupSelector
{
    public const ALL_GROUPS = -1;

    /** @var int[]|null */
    private ?array $allGroups = null;

    /**
     * @param CollectionFactory $groupCollectionFactory
     */
    public function __construct(private readonly CollectionFactory $groupCollectionFactory)
    {
    }

    /**
     * @param LabelInterface $label
     * @param bool $requiresCustomerGroup
     * @return int[]
     */
    public function forLabel(LabelInterface $label, bool $requiresCustomerGroup): array
    {
        if (!$requiresCustomerGroup) {
            return [self::ALL_GROUPS];
        }
        $groups = $label->getCustomerGroupIds();
        if ($groups !== []) {
            return array_values(array_unique(array_map('intval', $groups)));
        }
        if ($this->allGroups === null) {
            $this->allGroups = array_map('intval', $this->groupCollectionFactory->create()->getAllIds());
            sort($this->allGroups);
        }

        return $this->allGroups;
    }
}
