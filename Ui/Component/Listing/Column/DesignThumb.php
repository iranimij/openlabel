<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Ui\Component\Listing\Column;

use Iranimij\OpenLabel\Model\Design\ImageUrl;
use Iranimij\OpenLabel\Model\Design\Thumbnail;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Design thumbnail cell. The row fields are read with an optional prefix ("design_" in the labels grid).
 */
class DesignThumb extends Column
{
    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param Thumbnail $thumbnail
     * @param ImageUrl $imageUrl
     * @param array<string, mixed> $components
     * @param array<string, mixed> $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        private readonly Thumbnail $thumbnail,
        private readonly ImageUrl $imageUrl,
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
        $prefix = (string) ($this->getData('config/fieldPrefix') ?? '');
        $name = (string) $this->getData('name');
        foreach ($dataSource['data']['items'] ?? [] as $i => $item) {
            $dataSource['data']['items'][$i][$name] = $this->thumbnail->render([
                'type' => $item[$prefix . 'type'] ?? null,
                'shape' => $item[$prefix . 'shape'] ?? null,
                'bg_color' => $item[$prefix . 'bg_color'] ?? null,
                'text_color' => $item[$prefix . 'text_color'] ?? null,
                'border_color' => $item[$prefix . 'border_color'] ?? null,
                'text' => $item[$prefix . 'text'] ?? '',
                'alt_text' => $item[$prefix . 'alt_text'] ?? '',
                'image_url' => $this->imageUrl->get($item[$prefix . 'image_path'] ?? null),
            ]);
        }

        return $dataSource;
    }
}
