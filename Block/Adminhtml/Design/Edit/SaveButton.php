<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Block\Adminhtml\Design\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Save with a "Save & Continue" option; hidden for locked built-in designs.
 */
class SaveButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * @return array<string, mixed>
     */
    public function getButtonData(): array
    {
        if ($this->isSystem()) {
            return [];
        }

        return [
            'label' => __('Save'),
            'class' => 'save primary',
            'data_attribute' => $this->formAction([true]),
            'class_name' => \Magento\Ui\Component\Control\Container::SPLIT_BUTTON,
            'options' => [[
                'id_hard' => 'save_and_continue',
                'label' => __('Save & Continue'),
                'data_attribute' => $this->formAction([true, ['back' => 'edit']]),
            ]],
            'dropdown_button_aria_label' => __('Save options'),
            'sort_order' => 90,
        ];
    }

    /**
     * @param array<int, mixed> $params arguments of the form's save(redirect, data)
     * @return array<string, mixed>
     */
    private function formAction(array $params): array
    {
        return ['mage-init' => ['buttonAdapter' => ['actions' => [[
            'targetName' => 'openlabel_design_form.openlabel_design_form',
            'actionName' => 'save',
            'params' => $params,
        ]]]]];
    }
}
