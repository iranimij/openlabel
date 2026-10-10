<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Ui\Component\Listing\Column;

use Iranimij\OpenLabel\Model\Label\PlacementSummary;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * "Listing TR, Product TL" cell.
 */
class Placements extends Column
{
    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param PlacementSummary $summary
     * @param array<string, mixed> $components
     * @param array<string, mixed> $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        private readonly PlacementSummary $summary,
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
        $name = (string) $this->getData('name');
        foreach ($dataSource['data']['items'] ?? [] as $i => $item) {
            $dataSource['data']['items'][$i][$name] = $this->summary->format($item[$name] ?? null);
        }

        return $dataSource;
    }
}
