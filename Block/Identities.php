<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Block;

use Iranimij\OpenLabel\ViewModel\LabelRenderer;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Element\Context;

/**
 * Renders nothing; tags the cached page with `openlabel_<label_id>` and `openlabel_design_<design_id>` of every
 * label shown on it. The page cache collects identities after the layout output, so this block sees all labels
 * rendered anywhere on the page. Saving a label or a design then purges exactly these pages (Varnish included).
 */
class Identities extends AbstractBlock implements IdentityInterface
{
    /**
     * @param Context $context
     * @param LabelRenderer $labelRenderer
     * @param array<string, mixed> $data
     */
    public function __construct(
        Context $context,
        private readonly LabelRenderer $labelRenderer,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * @return string[]
     */
    public function getIdentities()
    {
        return $this->labelRenderer->getIdentities();
    }

    /**
     * @inheritDoc
     */
    protected function _toHtml()
    {
        return '';
    }
}
