<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Ui\Component\Listing\Column;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Edit link per row. Configure with data/config/editUrlPath and data/config/indexField.
 */
class RowActions extends Column
{
    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array<string, mixed> $components
     * @param array<string, mixed> $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        private readonly UrlInterface $urlBuilder,
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
        $path = (string) $this->getData('config/editUrlPath');
        $field = (string) $this->getData('config/indexField');
        $name = (string) $this->getData('name');
        foreach ($dataSource['data']['items'] ?? [] as $i => $item) {
            if (!isset($item[$field])) {
                continue;
            }
            $dataSource['data']['items'][$i][$name]['edit'] = [
                'href' => $this->urlBuilder->getUrl($path, ['id' => $item[$field]]),
                'label' => __('Edit'),
            ];
        }

        return $dataSource;
    }
}
