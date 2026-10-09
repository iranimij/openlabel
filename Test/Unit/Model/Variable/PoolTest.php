<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Test\Unit\Model\Variable;

use Iranimij\OpenLabel\Model\Variable\Pool;
use Iranimij\OpenLabel\Model\Variable\Processor;
use Iranimij\OpenLabel\Model\Variable\ProductPrices;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

class PoolTest extends TestCase
{
    public function testEveryBuiltInProcessorAnswersWithItsCode(): void
    {
        $objectManager = new ObjectManager($this);
        $classes = [
            Processor\SavePercent::class, Processor\SaveAmount::class, Processor\Price::class, Processor\SpecialPrice::class,
            Processor\FinalPrice::class, Processor\StockQty::class, Processor\NewFor::class, Processor\Sku::class,
            Processor\Attr::class, Processor\Br::class, Processor\SpecialEndsIn::class, Processor\SpecialEndDate::class,
            Processor\Rating::class, Processor\ReviewCount::class, Processor\SoldLast30d::class,
        ];
        $processors = array_map(static fn (string $class) => $objectManager->getObject($class, ['prices' => new ProductPrices()]), $classes);
        $processors[] = new \stdClass();

        $pool = new Pool($processors);

        self::assertSame([
            'SAVE_PERCENT', 'SAVE_AMOUNT', 'PRICE', 'SPECIAL_PRICE', 'FINAL_PRICE', 'STOCK_QTY', 'NEW_FOR', 'SKU', 'ATTR', 'BR',
            'SPECIAL_ENDS_IN', 'SPECIAL_END_DATE', 'RATING', 'REVIEW_COUNT', 'SOLD_LAST_30D',
        ], array_keys($pool->getAll()));
        self::assertInstanceOf(Processor\Sku::class, $pool->get('sku'));
        self::assertNull($pool->get('NOPE'));
    }
}
