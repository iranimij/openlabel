<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Model\Condition\Stock;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;

/**
 * Stock data without MSI: cataloginventory_stock_status (one default stock, website 0).
 */
class LegacyStockData implements StockDataInterface
{
    private const DEFAULT_STOCK_ID = 1;

    /**
     * @param StockRegistryInterface $stockRegistry
     */
    public function __construct(private readonly StockRegistryInterface $stockRegistry)
    {
    }

    /**
     * @inheritDoc
     */
    public function getTableName(int $websiteId): string
    {
        return 'cataloginventory_stock_status';
    }

    /**
     * @inheritDoc
     */
    public function getJoinCondition(string $alias, int $websiteId): string
    {
        return sprintf('%1$s.product_id = e.entity_id AND %1$s.website_id = 0 AND %1$s.stock_id = %2$d', $alias, self::DEFAULT_STOCK_ID);
    }

    /**
     * @inheritDoc
     */
    public function getQtyColumn(): string
    {
        return 'qty';
    }

    /**
     * @inheritDoc
     */
    public function getSalableColumn(): string
    {
        return 'stock_status';
    }

    /**
     * @inheritDoc
     */
    public function isSalable(ProductInterface $product, int $websiteId): bool
    {
        return (bool) $this->stockRegistry->getStockStatus((int) $product->getId())->getStockStatus();
    }

    /**
     * @inheritDoc
     */
    public function getSalableQty(ProductInterface $product, int $websiteId): float
    {
        return (float) $this->stockRegistry->getStockStatus((int) $product->getId())->getQty();
    }
}
