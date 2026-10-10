<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * "Regenerate CSS" button: posts the settings form (which carries the form key) to openlabel/css/regenerate
 * through `formaction`; a nested form would be dropped by the HTML parser. No JavaScript.
 */
class RegenerateCss extends Field
{
    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        return sprintf(
            '<button type="submit" id="openlabel-css-regenerate" class="action-default" formmethod="post"'
            . ' formnovalidate formaction="%s">%s</button>',
            $this->escapeUrl($this->getUrl('openlabel/css/regenerate')),
            $this->escapeHtml((string) __('Regenerate CSS'))
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
