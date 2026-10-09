<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Plugin\Indexer;

use Iranimij\OpenLabel\Model\Indexer\Scheduler;
use Magento\CatalogInventory\Model\Indexer\Stock\Action\Rows;

/**
 * Stock changes (also MSI default-source changes synced to the legacy stock) reach the index through the stock indexer.
 */
class StockRows
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
