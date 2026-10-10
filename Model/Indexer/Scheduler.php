<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Model\Indexer;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Mview\View\ChangelogInterface;

/**
 * Hands product ids to the OpenLabel indexer the way its mode wants: into the changelog when "Update by Schedule",
 * reindexed at once when "Update on Save". Used by the plugins on the price, stock and review updates (06 · F20).
 */
class Scheduler
{
    public const INDEXER_ID = 'openlabel_product';

    /**
     * @param IndexerRegistry $indexerRegistry
     * @param ResourceConnection $resource
     */
    public function __construct(
        private readonly IndexerRegistry $indexerRegistry,
        private readonly ResourceConnection $resource
    ) {
    }

    /**
     * @param int[] $productIds
     * @return void
     */
    public function scheduleProducts(array $productIds): void
    {
        $productIds = array_values(array_unique(array_map('intval', $productIds)));
        if ($productIds === []) {
            return;
        }
        $indexer = $this->indexerRegistry->get(self::INDEXER_ID);
        if ($indexer->isScheduled()) {
            $this->addToChangelog($indexer->getView()->getChangelog(), $productIds);
        } else {
            $indexer->reindexList($productIds);
        }
    }

    /**
     * @param ChangelogInterface $changelog
     * @param int[] $productIds
     * @return void
     */
    private function addToChangelog(ChangelogInterface $changelog, array $productIds): void
    {
        if (method_exists($changelog, 'addList')) {
            $changelog->addList($productIds);

            return;
        }
        // Older framework versions have no addList(): write the changelog rows directly.
        $connection = $this->resource->getConnection();
        $connection->insertArray(
            $this->resource->getTableName($changelog->getName()),
            [$changelog->getColumnName()],
            array_map(static fn (int $id): array => [$id], $productIds)
        );
    }

    /**
     * Everything changed (full price or stock reindex): mark the index invalid so the next cron or CLI run rebuilds it.
     *
     * @return void
     */
    public function scheduleFull(): void
    {
        $this->indexerRegistry->get(self::INDEXER_ID)->invalidate();
    }
}
