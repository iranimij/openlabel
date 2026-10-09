<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Model\Condition;

use Magento\Catalog\Model\Product;

/**
 * Number of approved reviews in the rule's store view.
 */
class ReviewCount extends AbstractBuiltIn
{
    use ReviewSummaryJoin;

    public const REVIEW_COUNT = 'review_count';

    /**
     * @inheritDoc
     */
    protected function attributeOptions(): array
    {
        return [self::REVIEW_COUNT => __('Number of reviews')];
    }

    /**
     * @inheritDoc
     */
    public function getMappedSqlField()
    {
        return self::REVIEW_ALIAS . '.reviews_count';
    }

    /**
     * @inheritDoc
     */
    protected function productValue(Product $product): mixed
    {
        $count = $product->getData('reviews_count');

        return $count === null || $count === '' ? null : (int) $count;
    }
}
