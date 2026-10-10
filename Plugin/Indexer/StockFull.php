<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Plugin\Indexer;

use Iranimij\OpenLabel\Model\Indexer\Scheduler;
use Magento\CatalogInventory\Model\Indexer\Stock\Action\Full;

/**
 * After a full stock reindex every stock label may have changed: invalidate the OpenLabel index.
 */
class StockFull
{
    /**
     * @param Scheduler $scheduler
     */
    public function __construct(private readonly Scheduler $scheduler)
    {
    }

    /**
     * @param Full $subject
     * @param mixed $result
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecute(Full $subject, mixed $result): mixed
    {
        $this->scheduler->scheduleFull();

        return $result;
    }
}
