<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Test\Unit\Model\Indexer;

use Iranimij\OpenLabel\Model\Indexer\Scheduler;
use Magento\Framework\Indexer\IndexerInterface;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Mview\View\Changelog;
use Magento\Framework\Mview\ViewInterface;
use PHPUnit\Framework\TestCase;

class SchedulerTest extends TestCase
{
    public function testScheduledIndexerGetsTheIdsInItsChangelog(): void
    {
        $changelog = $this->createMock(Changelog::class);
        $changelog->expects(self::once())->method('addList')->with([3, 5]);
        $view = $this->createStub(ViewInterface::class);
        $view->method('getChangelog')->willReturn($changelog);
        $indexer = $this->createMock(IndexerInterface::class);
        $indexer->method('isScheduled')->willReturn(true);
        $indexer->method('getView')->willReturn($view);
        $indexer->expects(self::never())->method('reindexList');

        $this->scheduler($indexer)->scheduleProducts([3, 5, 3]);
    }

    public function testUpdateOnSaveIndexerReindexesImmediately(): void
    {
        $indexer = $this->createMock(IndexerInterface::class);
        $indexer->method('isScheduled')->willReturn(false);
        $indexer->expects(self::once())->method('reindexList')->with([3, 5]);

        $this->scheduler($indexer)->scheduleProducts([3, 5]);
    }

    public function testEmptyListDoesNothing(): void
    {
        $indexer = $this->createMock(IndexerInterface::class);
        $indexer->expects(self::never())->method('isScheduled');

        $this->scheduler($indexer)->scheduleProducts([]);
    }

    public function testFullScheduleInvalidatesTheIndexer(): void
    {
        $indexer = $this->createMock(IndexerInterface::class);
        $indexer->expects(self::once())->method('invalidate');

        $this->scheduler($indexer)->scheduleFull();
    }

    private function scheduler(IndexerInterface $indexer): Scheduler
    {
        $registry = $this->createStub(IndexerRegistry::class);
        $registry->method('get')->with(Scheduler::INDEXER_ID)->willReturn($indexer);

        return new Scheduler($registry, $this->createStub(ResourceConnection::class));
    }
}
