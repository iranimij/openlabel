<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Api;

use Iranimij\OpenLabel\Model\Variable\Context;
use Magento\Catalog\Api\Data\ProductInterface;

/**
 * Variables that need data outside the loaded product (orders, review summaries) load it for a whole
 * listing in one query here, so rendering never adds a query per product.
 *
 * @api
 */
interface VariablePreloadInterface
{
    /**
     * @param ProductInterface[] $products
     * @param Context $context
     * @return void
     */
    public function preload(array $products, Context $context): void;
}
