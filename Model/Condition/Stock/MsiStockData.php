<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Model\Condition\Stock;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Stock data with MSI: the inventory_stock_<id> index of the stock assigned to the website's sales channel.
 *
 * MSI is optional (composer "suggest"), so its interfaces are resolved through the object manager at runtime
 * instead of the constructor. StockDataResolver only hands out this class when the MSI modules are enabled.
 */
class MsiStockData implements StockDataInterface
{
    private const STOCK_RESOLVER = 'Magento\InventorySalesApi\Api\StockResolverInterface';
    private const IS_SALABLE = 'Magento\InventorySalesApi\Api\IsProductSalableInterface';
    private const SALABLE_QTY = 'Magento\InventorySalesApi\Api\GetProductSalableQtyInterface';
    private const CHANNEL_WEBSITE = 'website';

    /** @var array<int, int> */
    private array $stockIds = [];

    /**
     * @param ObjectManagerInterface $objectManager
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        private readonly ObjectManagerInterface $objectManager,
        private readonly StoreManagerInterface $storeManager
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getTableName(int $websiteId): string
    {
        return 'inventory_stock_' . $this->stockId($websiteId);
    }

    /**
     * @inheritDoc
     */
    public function getJoinCondition(string $alias, int $websiteId): string
    {
        return $alias . '.sku = e.sku';
    }

    /**
     * @inheritDoc
     */
    public function getQtyColumn(): string
    {
        return 'quantity';
    }

    /**
     * @inheritDoc
     */
    public function getSalableColumn(): string
    {
        return 'is_salable';
    }

    /**
     * @inheritDoc
     */
    public function isSalable(ProductInterface $product, int $websiteId): bool
    {
        return (bool) $this->objectManager->get(self::IS_SALABLE)->execute($product->getSku(), $this->stockId($websiteId));
    }

    /**
     * @inheritDoc
     */
    public function getSalableQty(ProductInterface $product, int $websiteId): float
    {
        try {
            return (float) $this->objectManager->get(self::SALABLE_QTY)->execute($product->getSku(), $this->stockId($websiteId));
        } catch (\Exception) {
            // Composite products have no quantity of their own.
            return 0.0;
        }
    }

    /**
     * @param int $websiteId
     * @return int
     */
    private function stockId(int $websiteId): int
    {
        if (!isset($this->stockIds[$websiteId])) {
            $code = $this->storeManager->getWebsite($websiteId)->getCode();
            $stock = $this->objectManager->get(self::STOCK_RESOLVER)->execute(self::CHANNEL_WEBSITE, $code);
            $this->stockIds[$websiteId] = (int) $stock->getStockId();
        }

        return $this->stockIds[$websiteId];
    }
}
