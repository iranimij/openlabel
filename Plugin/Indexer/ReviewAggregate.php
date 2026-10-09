<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Plugin\Indexer;

use Iranimij\OpenLabel\Model\Indexer\Scheduler;
use Magento\Review\Model\ResourceModel\Review;
use Magento\Review\Model\Review as ReviewModel;

/**
 * A new or approved review changes rating and review count: refresh the product's rows (Update on Save mode;
 * Update by Schedule is covered by the review_entity_summary subscription).
 */
class ReviewAggregate
{
    /**
     * @param Scheduler $scheduler
     */
    public function __construct(private readonly Scheduler $scheduler)
    {
    }

    /**
     * @param Review $subject
     * @param mixed $result
     * @param ReviewModel $review
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterAggregate(Review $subject, mixed $result, ReviewModel $review): mixed
    {
        if ($review->getEntityPkValue()) {
            $this->scheduler->scheduleProducts([(int) $review->getEntityPkValue()]);
        }

        return $result;
    }
}
