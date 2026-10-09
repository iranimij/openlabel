<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Api;

use Iranimij\OpenLabel\Model\Variable\Context;
use Iranimij\OpenLabel\Model\Variable\Value;
use Magento\Catalog\Api\Data\ProductInterface;

/**
 * One label-text variable such as {SAVE_PERCENT} or {ATTR:color}. Register implementations in the
 * \Iranimij\OpenLabel\Model\Variable\Pool "processors" argument to add your own.
 *
 * @api
 */
interface VariableProcessorInterface
{
    /**
     * Variable name without braces, upper case, e.g. SAVE_PERCENT. For {ATTR:code} the code is ATTR.
     *
     * @return string
     */
    public function getCode(): string;

    /**
     * The raw, typed value for one product. Formatting (locale, currency, dates) and escaping happen in the Renderer.
     *
     * @param ProductInterface $product a loaded product, usually from the listing or product page collection
     * @param Context $context store, website and customer group of the request
     * @param string $argument text after the colon, e.g. the attribute code of {ATTR:color}
     * @return Value
     */
    public function getValue(ProductInterface $product, Context $context, string $argument = ''): Value;
}
