<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Model\Variable\Processor;

use Iranimij\OpenLabel\Api\VariableProcessorInterface;
use Iranimij\OpenLabel\Model\Variable\Context;
use Iranimij\OpenLabel\Model\Variable\ProductPrices;
use Iranimij\OpenLabel\Model\Variable\Value;
use Magento\Catalog\Api\Data\ProductInterface;

/**
 * {FINAL_PRICE}: Final price after special price and catalog rules (lowest child price for composites).
 */
class FinalPrice implements VariableProcessorInterface
{
    /**
     * @param ProductPrices $prices
     */
    public function __construct(private readonly ProductPrices $prices)
    {
    }

    /**
     * @inheritDoc
     */
    public function getCode(): string
    {
        return 'FINAL_PRICE';
    }

    /**
     * @inheritDoc
     */
    public function getValue(ProductInterface $product, Context $context, string $argument = ''): Value
    {
        return Value::currency($this->prices->final($product));
    }
}
