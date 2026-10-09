<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Test\Fixture;

use Iranimij\OpenLabel\Api\Data\LabelInterface;
use Iranimij\OpenLabel\Api\Data\LabelInterfaceFactory;
use Iranimij\OpenLabel\Api\Data\PlacementInterfaceFactory;
use Iranimij\OpenLabel\Api\LabelRepositoryInterface;
use Magento\Framework\DataObject;
use Magento\TestFramework\Fixture\RevertibleDataFixtureInterface;

/**
 * Integration-test fixture: an enabled label with one listing placement. 'design_id' is required.
 * 'placements' is a list of placement data arrays.
 */
class Label implements RevertibleDataFixtureInterface
{
    private const DEFAULTS = [
        'name' => 'Fixture label',
        'status' => LabelInterface::STATUS_ENABLED,
        'priority' => 0,
        'store_ids' => [],
        'customer_group_ids' => [],
        'apply_to_parent' => false,
        'hide_lower_priority' => false,
        'placements' => [['area' => 'listing', 'position' => 'tl']],
    ];

    /**
     * @param LabelInterfaceFactory $labelFactory
     * @param PlacementInterfaceFactory $placementFactory
     * @param LabelRepositoryInterface $labelRepository
     */
    public function __construct(
        private readonly LabelInterfaceFactory $labelFactory,
        private readonly PlacementInterfaceFactory $placementFactory,
        private readonly LabelRepositoryInterface $labelRepository
    ) {
    }

    /**
     * @inheritDoc
     */
    public function apply(array $data = []): ?DataObject
    {
        $data = array_merge(self::DEFAULTS, $data);
        $placements = [];
        foreach ($data['placements'] as $placementData) {
            $placement = $this->placementFactory->create();
            $placement->setData($placementData);
            $placements[] = $placement;
        }
        unset($data['placements']);
        $label = $this->labelFactory->create();
        $label->setData($data);
        $label->setPlacements($placements);

        return $this->labelRepository->save($label);
    }

    /**
     * @inheritDoc
     */
    public function revert(DataObject $data): void
    {
        $this->labelRepository->deleteById((int) $data->getData('label_id'));
    }
}
