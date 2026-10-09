<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Plugin\Indexer;

use Iranimij\OpenLabel\Model\Indexer\Scheduler;
use Magento\Catalog\Model\Indexer\Product\Price\Action\Full;

/**
 * After a full price reindex every price label may have changed: invalidate the OpenLabel index.
 */
class PriceFull
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
