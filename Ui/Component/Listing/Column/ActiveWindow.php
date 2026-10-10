<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Ui\Component\Listing\Column;

use Iranimij\OpenLabel\Model\Label\ActiveWindow as Window;
use Iranimij\OpenLabel\Model\Label\DateConverter;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * "Active now · From 2026-11-01" cell; dates in the shop timezone.
 */
class ActiveWindow extends Column
{
    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param Window $window
     * @param DateConverter $dateConverter
     * @param array<string, mixed> $components
     * @param array<string, mixed> $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        private readonly Window $window,
        private readonly DateConverter $dateConverter,
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
        $now = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
        foreach ($dataSource['data']['items'] ?? [] as $i => $item) {
            $from = $item['valid_from'] ?? null;
            $to = $item['valid_to'] ?? null;
            $state = $this->window->state((int) ($item['status'] ?? 0), $from, $to, $now);
            $dataSource['data']['items'][$i][$name] = $this->window->stateLabel($state) . ' · '
                . $this->window->describe($this->dateConverter->toLocal($from), $this->dateConverter->toLocal($to));
        }

        return $dataSource;
    }
}
