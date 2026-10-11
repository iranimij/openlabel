<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Model\Source;

use Iranimij\OpenLabel\Model\Design\PickerOptions;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * "Use a different design here": the label's own design (empty) or any other design.
 */
class DesignOverride implements OptionSourceInterface
{
    /**
     * @param PickerOptions $pickerOptions
     */
    public function __construct(
        private readonly PickerOptions $pickerOptions
    ) {
    }

    /**
     * @return array<int, array{value: string, label: string|\Magento\Framework\Phrase}>
     */
    public function toOptionArray(): array
    {
        $options = [['value' => '', 'label' => __('Same as the label')]];
        foreach ($this->pickerOptions->get() as $design) {
            $options[] = ['value' => (string) $design['value'], 'label' => (string) $design['label']];
        }

        return $options;
    }
}
