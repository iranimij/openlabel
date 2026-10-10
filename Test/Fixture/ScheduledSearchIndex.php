<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Test\Fixture;

use Magento\Framework\DataObject;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\TestFramework\Fixture\RevertibleDataFixtureInterface;

/**
 * Puts the search indexer on "Update by Schedule" while product fixtures are created, so product saves do not
 * write to the search engine. CI runs 2.4.7 against Elasticsearch 8, which rejects 2.4.7's bulk requests.
 * List it before any product fixture.
 */
class ScheduledSearchIndex implements RevertibleDataFixtureInterface
{
    private const INDEXER = 'catalogsearch_fulltext';

    /**
     * @param IndexerRegistry $indexerRegistry
     */
    public function __construct(private readonly IndexerRegistry $indexerRegistry)
    {
    }

    /**
     * @inheritDoc
     */
    public function apply(array $data = []): ?DataObject
    {
        $indexer = $this->indexerRegistry->get(self::INDEXER);
        $wasScheduled = $indexer->isScheduled();
        $indexer->setScheduled(true);

        return new DataObject(['was_scheduled' => $wasScheduled]);
    }

    /**
     * @inheritDoc
     */
    public function revert(DataObject $data): void
    {
        $this->indexerRegistry->get(self::INDEXER)->setScheduled((bool) $data->getData('was_scheduled'));
    }
}
