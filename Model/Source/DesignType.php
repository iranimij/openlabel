<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Design type options.
 */
class DesignType implements OptionSourceInterface
{
    /**
     * @return array<int, array{value: int|string, label: \Magento\Framework\Phrase}>
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => 'text', 'label' => __('Text')],
            ['value' => 'image', 'label' => __('Image')],
            ['value' => 'shape', 'label' => __('Shape')],
        ];
    }
}
