<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Test\Fixture;

use Iranimij\OpenLabel\Api\Data\DesignInterface;
use Iranimij\OpenLabel\Api\Data\DesignInterfaceFactory;
use Iranimij\OpenLabel\Api\DesignRepositoryInterface;
use Magento\Framework\DataObject;
use Magento\TestFramework\Fixture\RevertibleDataFixtureInterface;

/**
 * Integration-test fixture: a text design. Pass 'store_texts' => [storeId => ['text' => .., 'alt_text' => ..]] to translate.
 */
class Design implements RevertibleDataFixtureInterface
{
    private const DEFAULTS = [
        'name' => 'Fixture design',
        'type' => DesignInterface::TYPE_TEXT,
        'shape' => 'pill',
        'bg_color' => '#e11d48',
        'text_color' => '#ffffff',
        'size_mode' => DesignInterface::SIZE_MODE_PERCENT,
        'width' => 18,
        'store_texts' => [0 => ['text' => 'Sale', 'alt_text' => null, 'tooltip' => null]],
    ];

    /**
     * @param DesignInterfaceFactory $designFactory
     * @param DesignRepositoryInterface $designRepository
     */
    public function __construct(
        private readonly DesignInterfaceFactory $designFactory,
        private readonly DesignRepositoryInterface $designRepository
    ) {
    }

    /**
     * @inheritDoc
     */
    public function apply(array $data = []): ?DataObject
    {
        $data = array_merge(self::DEFAULTS, $data);
        $design = $this->designFactory->create();
        $storeTexts = $data['store_texts'];
        unset($data['store_texts']);
        $design->setData($data);
        $design->setStoreTexts($storeTexts);

        return $this->designRepository->save($design);
    }

    /**
     * @inheritDoc
     */
    public function revert(DataObject $data): void
    {
        $this->designRepository->deleteById((int) $data->getData('design_id'));
    }
}
