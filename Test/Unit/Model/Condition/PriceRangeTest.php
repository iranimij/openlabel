<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Test\Unit\Model\Condition;

use Iranimij\OpenLabel\Model\Condition\PriceRange;

class PriceRangeTest extends ConditionTestCase
{
    public function testFinalPriceComparesTheGroupSpecificFinalPrice(): void
    {
        $condition = $this->condition(PriceRange::class, 'final_price', '<=', '50');

        self::assertTrue($condition->validate($this->product(['price' => 60.0, 'final_price' => 45.0])));
        self::assertFalse($condition->validate($this->product(['price' => 60.0, 'final_price' => 55.0])));
    }

    public function testRegularPriceIgnoresDiscounts(): void
    {
        $condition = $this->condition(PriceRange::class, 'price', '>=', '60');

        self::assertTrue($condition->validate($this->product(['price' => 60.0, 'final_price' => 45.0])));
    }

    public function testSqlMapsToThePriceIndexAndNeedsAGroup(): void
    {
        $condition = $this->condition(PriceRange::class, 'final_price', '<=', '50');

        self::assertSame('ol_price.final_price', (string) $condition->getMappedSqlField());
        self::assertArrayHasKey('ol_price', $condition->getTablesToJoin());
        self::assertTrue($condition->requiresCustomerGroup());
    }
}
