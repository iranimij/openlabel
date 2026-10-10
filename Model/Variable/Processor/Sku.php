<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Model\Variable\Processor;

use Iranimij\OpenLabel\Api\VariableProcessorInterface;
use Iranimij\OpenLabel\Model\Variable\Context;
use Iranimij\OpenLabel\Model\Variable\Value;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;

/**
 * {SKU}
 */
class Sku implements VariableProcessorInterface
{
    /**
     * @inheritDoc
     */
    public function getCode(): string
    {
        return 'SKU';
    }

    /**
     * @inheritDoc
     */
    public function getValue(ProductInterface $product, Context $context, string $argument = ''): Value
    {
        // The loaded data is the source of truth; Product::getSku() asks the type instance, which composites customise.
        $sku = $product instanceof Product ? $product->getData('sku') : $product->getSku();

        return Value::text((string) $sku);
    }
}
