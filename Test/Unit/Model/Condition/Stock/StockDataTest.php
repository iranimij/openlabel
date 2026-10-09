<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Test\Unit\Model\Condition\Stock;

use Iranimij\OpenLabel\Model\Condition\Stock\LegacyStockData;
use Iranimij\OpenLabel\Model\Condition\Stock\MsiStockData;
use Magento\Catalog\Model\Product;
use Magento\CatalogInventory\Api\Data\StockStatusInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\TestCase;

class StockDataTest extends TestCase
{
    public function testLegacyStockReadsTheDefaultStockStatus(): void
    {
        $status = $this->createStub(StockStatusInterface::class);
        $status->method('getStockStatus')->willReturn(1);
        $status->method('getQty')->willReturn(5.0);
        $registry = $this->createStub(StockRegistryInterface::class);
        $registry->method('getStockStatus')->willReturn($status);
        $legacy = new LegacyStockData($registry);
        $product = $this->product();

        self::assertSame('cataloginventory_stock_status', $legacy->getTableName(1));
        self::assertSame('ol_stock.product_id = e.entity_id AND ol_stock.website_id = 0 AND ol_stock.stock_id = 1', $legacy->getJoinCondition('ol_stock', 1));
        self::assertSame('qty', $legacy->getQtyColumn());
        self::assertSame('stock_status', $legacy->getSalableColumn());
        self::assertTrue($legacy->isSalable($product, 1));
        self::assertSame(5.0, $legacy->getSalableQty($product, 1));
    }

    public function testMsiStockResolvesTheWebsiteStockOnceAndHandlesCompositeQuantities(): void
    {
        $calls = 0;
        $resolver = new class ($calls) {
            public function __construct(private int &$calls)
            {
            }
            public function execute(string $type, string $code): object
            {
                $this->calls++;

                return new class {
                    public function getStockId(): int
                    {
                        return 7;
                    }
                };
            }
        };
        $salable = new class {
            public function execute(string $sku, int $stockId): bool
            {
                return $sku === 'A' && $stockId === 7;
            }
        };
        $qty = new class {
            public function execute(string $sku, int $stockId): float
            {
                if ($sku === 'C') {
                    throw new \RuntimeException('composite');
                }

                return 4.0;
            }
        };
        $objectManager = $this->createStub(ObjectManagerInterface::class);
        $objectManager->method('get')->willReturnCallback(static fn (string $class) => match (true) {
            str_ends_with($class, 'StockResolverInterface') => $resolver,
            str_ends_with($class, 'IsProductSalableInterface') => $salable,
            default => $qty,
        });
        $website = $this->createStub(WebsiteInterface::class);
        $website->method('getCode')->willReturn('base');
        $storeManager = $this->createStub(StoreManagerInterface::class);
        $storeManager->method('getWebsite')->willReturn($website);
        $msi = new MsiStockData($objectManager, $storeManager);

        self::assertSame('inventory_stock_7', $msi->getTableName(1));
        self::assertSame('ol_stock.sku = e.sku', $msi->getJoinCondition('ol_stock', 1));
        self::assertSame('quantity', $msi->getQtyColumn());
        self::assertSame('is_salable', $msi->getSalableColumn());
        self::assertTrue($msi->isSalable($this->product('A'), 1));
        self::assertSame(4.0, $msi->getSalableQty($this->product('A'), 1));
        self::assertSame(0.0, $msi->getSalableQty($this->product('C'), 1), 'composites have no quantity of their own');
        self::assertSame(1, $calls, 'the stock id of a website is resolved once');
    }

    private function product(string $sku = 'A'): Product
    {
        $product = $this->createStub(Product::class);
        $product->method('getId')->willReturn(3);
        $product->method('getSku')->willReturn($sku);

        return $product;
    }
}
