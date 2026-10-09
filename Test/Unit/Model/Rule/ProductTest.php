<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Test\Unit\Model\Rule;

use Iranimij\OpenLabel\Model\Rule\Condition\Product;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

class ProductTest extends TestCase
{
    public function testLoadsAttributeOptionsAndKeepsItsOwnType(): void
    {
        $resource = $this->createStub(ProductResource::class);
        $resource->method('loadAllAttributes')->willReturnSelf();
        $resource->method('getAttributesByCode')->willReturn([]);
        /** @var Product $condition */
        $condition = (new ObjectManager($this))->getObject(Product::class, ['productResource' => $resource]);

        $condition->loadAttributeOptions();

        self::assertSame(Product::class, $condition->getType());
        self::assertArrayHasKey('sku', $condition->getAttributeOption());
    }
}
