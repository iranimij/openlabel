<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Test\Unit\Model\Condition;

use Iranimij\OpenLabel\Model\Condition\OnSale;

class OnSaleTest extends ConditionTestCase
{
    public function testOnSaleIsTrueWhenFinalPriceIsBelowRegularPrice(): void
    {
        $condition = $this->condition(OnSale::class, 'on_sale', '==', '1');

        self::assertTrue($condition->validate($this->product(['price' => 100.0, 'final_price' => 80.0])));
        self::assertFalse($condition->validate($this->product(['price' => 100.0, 'final_price' => 100.0])));
    }

    public function testNotOnSaleMatchesFullPriceProducts(): void
    {
        $condition = $this->condition(OnSale::class, 'on_sale', '==', '0');

        self::assertTrue($condition->validate($this->product(['price' => 100.0, 'final_price' => 100.0])));
    }

    public function testDiscountPercentIsRoundedHalfUpBeforeComparing(): void
    {
        $condition = $this->condition(OnSale::class, 'discount_percent', '>=', '10');

        self::assertTrue($condition->validate($this->product(['price' => 100.0, 'final_price' => 90.4])), '9.6 % rounds to 10');
        self::assertFalse($condition->validate($this->product(['price' => 100.0, 'final_price' => 90.6])), '9.4 % rounds to 9');
    }

    public function testDiscountAmountComparesAbsoluteSaving(): void
    {
        $condition = $this->condition(OnSale::class, 'discount_amount', '>=', '15');

        self::assertTrue($condition->validate($this->product(['price' => 100.0, 'final_price' => 85.0])));
        self::assertFalse($condition->validate($this->product(['price' => 100.0, 'final_price' => 86.0])));
    }

    public function testZeroPriceNeverCountsAsOnSale(): void
    {
        $condition = $this->condition(OnSale::class, 'discount_percent', '>=', '1');

        self::assertFalse($condition->validate($this->product(['price' => 0.0, 'final_price' => 0.0])));
    }

    public function testSqlUsesThePriceIndexOfTheRulesWebsiteAndGroup(): void
    {
        $condition = $this->condition(OnSale::class, 'discount_percent', '>=', '10');

        $joins = $condition->getTablesToJoin();
        self::assertArrayHasKey('ol_price', $joins);
        self::assertSame('catalog_product_index_price', $joins['ol_price']['name']);
        self::assertStringContainsString('ol_price.website_id = 1', $joins['ol_price']['condition']);
        self::assertStringContainsString('ol_price.customer_group_id = 2', $joins['ol_price']['condition']);
        self::assertInstanceOf(\Zend_Db_Expr::class, $condition->getMappedSqlField());
        self::assertStringContainsString('ROUND(', (string) $condition->getMappedSqlField());
        self::assertTrue($condition->requiresCustomerGroup());
    }

    public function testOffersThreeAttributes(): void
    {
        $condition = $this->condition(OnSale::class, 'on_sale', '==', '1');

        self::assertSame(['on_sale', 'discount_percent', 'discount_amount'], array_keys($condition->loadAttributeOptions()->getAttributeOption()));
        self::assertSame('select', $condition->getInputType());
        self::assertSame('numeric', $condition->setAttribute('discount_amount')->getInputType());
    }
}
