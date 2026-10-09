<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Test\Unit\Model\Condition;

use Iranimij\OpenLabel\Model\Condition\ConditionPool;
use Iranimij\OpenLabel\Model\Condition\OnSale;
use Iranimij\OpenLabel\Model\Condition\Stock;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Rule\Model\ConditionFactory;
use PHPUnit\Framework\TestCase;

class ConditionPoolTest extends TestCase
{
    public function testCreatesOneInstancePerConfiguredTypeAndSkipsForeignClasses(): void
    {
        $objectManager = new ObjectManager($this);
        $factory = $this->createStub(ConditionFactory::class);
        $factory->method('create')->willReturnCallback(static fn (string $type) => $type === \stdClass::class ? new \stdClass() : $objectManager->getObject($type));
        $pool = new ConditionPool($factory, ['on_sale' => OnSale::class, 'stock' => Stock::class, 'foreign' => \stdClass::class]);

        $conditions = $pool->getConditions();

        self::assertCount(2, $conditions);
        self::assertInstanceOf(OnSale::class, $conditions[0]);
        self::assertInstanceOf(Stock::class, $conditions[1]);
        self::assertSame(['on_sale' => OnSale::class, 'stock' => Stock::class, 'foreign' => \stdClass::class], $pool->getTypes());
    }
}
