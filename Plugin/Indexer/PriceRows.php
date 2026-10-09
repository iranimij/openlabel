<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Plugin\Indexer;

use Iranimij\OpenLabel\Model\Indexer\Scheduler;
use Magento\Catalog\Model\Indexer\Product\Price\Action\Rows;

/**
 * Price changes (special price, catalog price rules, tier prices) reach the index through the price indexer,
 * not through mview subscriptions on index tables (06 · F20).
 */
class PriceRows
{
    /**
     * @param Scheduler $scheduler
     */
    public function __construct(private readonly Scheduler $scheduler)
    {
    }

    /**
     * @param Rows $subject
     * @param mixed $result
     * @param int[]|null $ids
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecute(Rows $subject, mixed $result, mixed $ids = null): mixed
    {
        if (is_array($ids)) {
            $this->scheduler->scheduleProducts($ids);
        }

        return $result;
    }
}
