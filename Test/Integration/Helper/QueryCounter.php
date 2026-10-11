<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Test\Integration\Helper;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\Pdo\Mysql;

/**
 * Counts the SQL statements a callback issues on the default connection (performance budget, 02 · Architecture §10).
 */
class QueryCounter
{
    /**
     * @param ResourceConnection $resource
     */
    public function __construct(private readonly ResourceConnection $resource)
    {
    }

    /** @var string[] SQL of the last counted callback, for assertion messages */
    public array $last = [];

    /**
     * @param callable $callback
     * @return int number of queries the callback issued
     */
    public function count(callable $callback): int
    {
        /** @var Mysql $connection */
        $connection = $this->resource->getConnection();
        $profiler = $connection->getProfiler();
        $wasEnabled = $profiler->getEnabled();
        $profiler->setEnabled(true);
        $profiler->clear();
        try {
            $callback();
            $this->last = array_map(
                static fn ($query): string => (string) $query->getQuery(),
                $profiler->getQueryProfiles() ?: []
            );

            return (int) $profiler->getTotalNumQueries();
        } finally {
            $profiler->clear();
            $profiler->setEnabled($wasEnabled);
        }
    }
}
