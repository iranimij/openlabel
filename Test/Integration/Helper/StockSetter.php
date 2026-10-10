<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Test\Integration\Helper;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Indexer\IndexerRegistry;

/**
 * Sets legacy stock quantities directly and rebuilds the stock and price indexes, in that order.
 * The product fixture's stock_item handling differs between 2.4.7, 2.4.8 and 2.4.9; SQL behaves the same everywhere.
 */
class StockSetter
{
    /**
     * @param ResourceConnection $resource
     * @param IndexerRegistry $indexerRegistry
     */
    public function __construct(
        private readonly ResourceConnection $resource,
        private readonly IndexerRegistry $indexerRegistry
    ) {
    }

    /**
     * @param array<int, array{float, bool}> $stock product id => [qty, in stock]
     * @return void
     */
    public function apply(array $stock): void
    {
        $connection = $this->resource->getConnection();
        foreach ($stock as $productId => [$qty, $inStock]) {
            $connection->update(
                $this->resource->getTableName('cataloginventory_stock_item'),
                ['qty' => $qty, 'is_in_stock' => (int) $inStock, 'manage_stock' => 1, 'use_config_manage_stock' => 0],
                ['product_id = ?' => $productId]
            );
        }
        $this->reindex();
    }

    /**
     * Stock first, then prices: the price index drops out-of-stock products based on the stock status.
     *
     * @return void
     */
    public function reindex(): void
    {
        foreach (['cataloginventory_stock', 'catalogrule_rule', 'catalog_product_price', 'catalogrule_product'] as $id) {
            $this->indexerRegistry->get($id)->reindexAll();
        }
    }
}
