<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Block\Adminhtml\System\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Indexer\IndexerRegistry;

/**
 * Read-only hint in the OpenLabel settings: the current mode of the openlabel_product indexer and the
 * recommendation (Update by Schedule, so product saves stay fast).
 */
class IndexMode extends Field
{
    /**
     * @param Context $context
     * @param IndexerRegistry $indexerRegistry
     * @param array<string, mixed> $data
     */
    public function __construct(
        Context $context,
        private readonly IndexerRegistry $indexerRegistry,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $scheduled = $this->indexerRegistry->get('openlabel_product')->isScheduled();
        $current = $scheduled ? __('Update by Schedule') : __('Update on Save');
        $advice = $scheduled
            ? __('Recommended. Labels follow product, price and stock changes within a minute through cron.')
            : __('Switch to "Update by Schedule" in System › Index Management so product saves stay fast; labels then follow changes within a minute through cron.');

        return sprintf(
            '<p class="ol-index-mode"><strong>%s</strong><br>%s</p>',
            $this->escapeHtml((string) __('Currently: %1', $current)),
            $this->escapeHtml((string) $advice)
        );
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _renderScopeLabel(AbstractElement $element)
    {
        return '';
    }
}
