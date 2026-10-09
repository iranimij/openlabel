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
 * {REVIEW_COUNT}: number of approved reviews in the store view; empty without reviews.
 */
class ReviewCount implements VariableProcessorInterface, VariablePreloadInterface
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
        return 'REVIEW_COUNT';
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
        $count = $this->reviewSummary->read($product, $context, 'reviews_count');

        return $count === null ? Value::empty() : Value::number($count);
    }
}
