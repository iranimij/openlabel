<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * The nine positions on the product image.
 */
class Position implements OptionSourceInterface
{
    /**
     * @return array<int, array{value: int|string, label: \Magento\Framework\Phrase}>
     */
    public function toOptionArray(): array
    {
        $options = [];
        $labels = [
            'tl' => __('Top left'),
            'tc' => __('Top centre'),
            'tr' => __('Top right'),
            'ml' => __('Middle left'),
            'mc' => __('Centre'),
            'mr' => __('Middle right'),
            'bl' => __('Bottom left'),
            'bc' => __('Bottom centre'),
            'br' => __('Bottom right'),
        ];
        foreach ($labels as $value => $label) {
            $options[] = ['value' => (string) $value, 'label' => $label];
        }

        return $options;
    }
}
