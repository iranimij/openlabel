<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Block\Adminhtml\Label\Edit;

use Magento\Backend\Block\Widget\Context;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magento\Ui\Component\Control\Container;

/**
 * Label form save bar: Back, Delete, and Save with "Save & Continue" and "Save & Duplicate".
 * One provider class per button is the core convention; the button kind comes from the constructor argument.
 */
class Buttons implements ButtonProviderInterface
{
    private const FORM = 'openlabel_label_form.openlabel_label_form';

    /**
     * @param Context $context
     * @param string $kind back | delete | save
     */
    public function __construct(
        private readonly Context $context,
        private readonly string $kind = 'save'
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function getButtonData(): array
    {
        $id = (int) $this->context->getRequest()->getParam('id');
        $url = $this->context->getUrlBuilder();

        return match ($this->kind) {
            'back' => [
                'label' => __('Back'),
                'on_click' => sprintf("location.href = '%s';", $url->getUrl('*/*/')),
                'class' => 'back',
                'sort_order' => 10,
            ],
            'delete' => $id === 0 ? [] : [
                'label' => __('Delete'),
                'class' => 'delete',
                'on_click' => sprintf(
                    "deleteConfirm('%s', '%s', {data: {}})",
                    __('Delete this label? It disappears from the shop right away.'),
                    $url->getUrl('*/*/delete', ['id' => $id])
                ),
                'sort_order' => 20,
            ],
            default => [
                'label' => __('Save'),
                'class' => 'save primary',
                'data_attribute' => $this->formAction([true]),
                'class_name' => Container::SPLIT_BUTTON,
                'options' => [
                    [
                        'id_hard' => 'save_and_continue',
                        'label' => __('Save & Continue'),
                        'data_attribute' => $this->formAction([true, ['back' => 'edit']]),
                    ],
                    [
                        'id_hard' => 'save_and_duplicate',
                        'label' => __('Save & Duplicate'),
                        'data_attribute' => $this->formAction([true, ['back' => 'duplicate']]),
                    ],
                ],
                'dropdown_button_aria_label' => __('Save options'),
                'sort_order' => 90,
            ],
        };
    }

    /**
     * @param array<int, mixed> $params
     * @return array<string, mixed>
     */
    private function formAction(array $params): array
    {
        return ['mage-init' => ['buttonAdapter' => ['actions' => [[
            'targetName' => self::FORM,
            'actionName' => 'save',
            'params' => $params,
        ]]]]];
    }
}
