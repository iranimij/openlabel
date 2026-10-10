<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Design shapes.
 */
class Shape implements OptionSourceInterface
{
    /**
     * @return array<int, array{value: int|string, label: \Magento\Framework\Phrase}>
     */
    public function toOptionArray(): array
    {
        $options = [];
        $labels = [
            'rectangle' => __('Rectangle'),
            'pill' => __('Pill'),
            'circle' => __('Circle'),
            'ribbon' => __('Ribbon'),
            'corner' => __('Corner'),
        ];
        foreach ($labels as $value => $label) {
            $options[] = ['value' => (string) $value, 'label' => $label];
        }

        return $options;
    }
}
