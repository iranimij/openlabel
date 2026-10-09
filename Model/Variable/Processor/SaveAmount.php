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
 * {SAVE_AMOUNT}: Discount as a currency amount; 0 without a discount.
 */
class SaveAmount implements VariableProcessorInterface
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
        return 'SAVE_AMOUNT';
    }

    /**
     * @inheritDoc
     */
    public function getValue(ProductInterface $product, Context $context, string $argument = ''): Value
    {
        $regular = $this->prices->regular($product);

        return Value::currency(max(0.0, $regular - $this->prices->final($product)));
    }
}
