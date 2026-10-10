<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Store views cell from the store_ids CSV (empty = all store views).
 */
class Stores extends Column
{
    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param StoreManagerInterface $storeManager
     * @param array<string, mixed> $components
     * @param array<string, mixed> $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        private readonly StoreManagerInterface $storeManager,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * @param array<string, mixed> $dataSource
     * @return array<string, mixed>
     */
    public function prepareDataSource(array $dataSource)
    {
        $names = [];
        foreach ($this->storeManager->getStores() as $store) {
            $names[(int) $store->getId()] = (string) $store->getName();
        }
        $name = (string) $this->getData('name');
        foreach ($dataSource['data']['items'] ?? [] as $i => $item) {
            $ids = array_filter(explode(',', (string) ($item['store_ids'] ?? '')), static fn (string $id): bool => $id !== '');
            $dataSource['data']['items'][$i][$name] = $ids === []
                ? (string) __('All store views')
                : implode(', ', array_map(static fn ($id): string => $names[(int) $id] ?? '#' . $id, $ids));
        }

        return $dataSource;
    }
}
