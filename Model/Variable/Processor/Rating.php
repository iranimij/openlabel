<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Model\Variable\Processor;

use Iranimij\OpenLabel\Api\VariablePreloadInterface;
use Iranimij\OpenLabel\Api\VariableProcessorInterface;
use Iranimij\OpenLabel\Model\Variable\Context;
use Iranimij\OpenLabel\Model\Variable\Value;
use Magento\Catalog\Api\Data\ProductInterface;

/**
 * {RATING}: average rating in stars with one decimal; empty without reviews.
 */
class Rating implements VariableProcessorInterface, VariablePreloadInterface
{
    /**
     * @param ReviewSummary $reviewSummary
     */
    public function __construct(private readonly ReviewSummary $reviewSummary)
    {
    }

    /**
     * @inheritDoc
     */
    public function getCode(): string
    {
        return 'RATING';
    }

    /**
     * @inheritDoc
     */
    public function preload(array $products, Context $context): void
    {
        $this->reviewSummary->preload($products, $context);
    }

    /**
     * @inheritDoc
     */
    public function getValue(ProductInterface $product, Context $context, string $argument = ''): Value
    {
        $percent = $this->reviewSummary->read($product, $context, 'rating_summary');

        return $percent === null ? Value::empty() : Value::number(round($percent / 20, 1), 1);
    }
}
