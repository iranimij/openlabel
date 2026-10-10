<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Test\Unit\Model\Condition;

use Iranimij\OpenLabel\Model\Condition\OnSale;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Framework\DataObject;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

class AbstractBuiltInTest extends TestCase
{
    public function testBooleanAttributesOfferYesNoAndOthersAreNumeric(): void
    {
        /** @var OnSale $condition */
        $condition = (new ObjectManager($this))->getObject(OnSale::class);

        $condition->setAttribute('on_sale');
        self::assertSame(['1', '0'], array_column($condition->getValueSelectOptions(), 'value'));
        self::assertSame('select', $condition->getValueElementType());

        $condition->setAttribute('discount_percent');
        self::assertSame([], $condition->getValueSelectOptions());
        self::assertSame('text', $condition->getValueElementType());
        self::assertFalse($condition->isDateRelative());
        self::assertSame($condition, $condition->collectValidatedAttributes($this->createStub(Collection::class)));
    }

    public function testOnlyProductsCanBeValidatedAndContextDefaultsToZeroWithoutARule(): void
    {
        /** @var OnSale $condition */
        $condition = (new ObjectManager($this))->getObject(OnSale::class);
        $condition->setAttribute('on_sale')->setOperator('==')->setValue('1');

        self::assertFalse($condition->validate($this->createStub(AbstractModel::class)));
        self::assertStringContainsString('ol_price.website_id = 0 AND ol_price.customer_group_id = 0', $condition->getTablesToJoin()['ol_price']['condition']);
        $condition->setRule(new DataObject());
        self::assertStringContainsString('ol_price.website_id = 0', $condition->getTablesToJoin()['ol_price']['condition'], 'a foreign rule object counts as no context');
    }
}
