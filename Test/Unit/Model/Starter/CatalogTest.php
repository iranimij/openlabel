<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Test\Unit\Model\Starter;

use Iranimij\OpenLabel\Model\Design\SystemDesignCatalog;
use Iranimij\OpenLabel\Model\Starter\Catalog;
use PHPUnit\Framework\TestCase;

class CatalogTest extends TestCase
{
    public function testThreeStartersInTheOrderOfTheUxSpec(): void
    {
        self::assertSame(['sale', 'new', 'low_stock'], array_keys((new Catalog())->getStarters()));
    }

    public function testEachStarterUsesAnExistingBuiltInDesign(): void
    {
        $designs = (new SystemDesignCatalog())->getDefinitions();

        foreach ((new Catalog())->getStarters() as $key => $starter) {
            self::assertArrayHasKey($starter['design'], $designs, $key);
        }
    }

    public function testSaleStarter(): void
    {
        $sale = (new Catalog())->getStarters()['sale'];

        self::assertSame('Sale -{SAVE_PERCENT}%', $sale['name']);
        self::assertSame(['on_sale' => '1', 'on_sale_min' => ''], $sale['quick']);
        self::assertSame('tl', $sale['position'], 'top start, free on Hyvä 1.4 cards');
    }

    public function testNewAndLowStockStarters(): void
    {
        $starters = (new Catalog())->getStarters();

        self::assertSame(['is_new' => '1', 'new_days' => '30'], $starters['new']['quick']);
        self::assertSame('tr', $starters['new']['position']);
        self::assertSame(['low_stock' => '1', 'low_stock_qty' => '5'], $starters['low_stock']['quick']);
        self::assertSame('bl', $starters['low_stock']['position']);
        self::assertSame('Only {STOCK_QTY} left', $starters['low_stock']['name']);
    }
}
