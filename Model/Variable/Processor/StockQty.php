<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Model\Variable\Processor;

use Iranimij\OpenLabel\Api\VariableProcessorInterface;
use Iranimij\OpenLabel\Model\Condition\Stock\StockDataResolver;
use Iranimij\OpenLabel\Model\Variable\Context;
use Iranimij\OpenLabel\Model\Variable\Value;
use Magento\Catalog\Api\Data\ProductInterface;

/**
 * {STOCK_QTY}: salable quantity (MSI-aware); empty for composite products, which have no quantity of their own.
 */
class StockQty implements VariableProcessorInterface
{
    private const QUANTITY_TYPES = ['simple', 'virtual', 'downloadable'];

    /**
     * @param StockDataResolver $stockDataResolver
     */
    public function __construct(private readonly StockDataResolver $stockDataResolver)
    {
    }

    /**
     * @inheritDoc
     */
    public function getCode(): string
    {
        return 'STOCK_QTY';
    }

    /**
     * @inheritDoc
     */
    public function getValue(ProductInterface $product, Context $context, string $argument = ''): Value
    {
        $type = (string) $product->getTypeId();
        if ($type !== '' && !in_array($type, self::QUANTITY_TYPES, true)) {
            return Value::empty();
        }

        return Value::number($this->stockDataResolver->get()->getSalableQty($product, $context->websiteId));
    }
}
