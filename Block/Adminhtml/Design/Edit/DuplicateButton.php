<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Block\Adminhtml\Design\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class DuplicateButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * @return array<string, mixed>
     */
    public function getButtonData(): array
    {
        if ($this->getDesignId() === 0) {
            return [];
        }
        $system = $this->isSystem();

        return [
            'label' => $system ? __('Duplicate to edit') : __('Duplicate'),
            'class' => $system ? 'primary' : '',
            'on_click' => sprintf("location.href = '%s';", $this->getUrl('*/*/duplicate', ['id' => $this->getDesignId()])),
            'sort_order' => 30,
        ];
    }
}
