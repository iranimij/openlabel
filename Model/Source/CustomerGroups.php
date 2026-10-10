<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Model\Source;

use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * All customer groups including NOT LOGGED IN (id 0).
 */
class CustomerGroups implements OptionSourceInterface
{
    /**
     * @param GroupRepositoryInterface $groupRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        private readonly GroupRepositoryInterface $groupRepository,
        private readonly SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public function toOptionArray(): array
    {
        $options = [];
        foreach ($this->groupRepository->getList($this->searchCriteriaBuilder->create())->getItems() as $group) {
            $options[] = ['value' => (string) $group->getId(), 'label' => (string) $group->getCode()];
        }

        return $options;
    }
}
