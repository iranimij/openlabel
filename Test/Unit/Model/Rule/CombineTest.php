<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Test\Unit\Model\Rule;

use Iranimij\OpenLabel\Model\Condition\ConditionPool;
use Iranimij\OpenLabel\Model\Condition\OnSale;
use Iranimij\OpenLabel\Model\Rule\Condition\Combine;
use Iranimij\OpenLabel\Model\Rule\Condition\Product;
use Iranimij\OpenLabel\Model\Rule\Condition\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Framework\DataObject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

class CombineTest extends TestCase
{
    private ObjectManager $objectManager;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
    }

    public function testNewChildOptionsListBuiltInsAndProductAttributesWithoutExcludedOnes(): void
    {
        $combine = $this->combine(['sku', 'attribute_set_id', 'category_ids']);

        $options = $combine->getNewChildSelectOptions();

        $labels = array_map(static fn (array $o): string => (string) $o['label'], $options);
        self::assertContains('OpenLabel', $labels);
        self::assertContains('Product Attribute', $labels);
        $byLabel = array_combine($labels, $options);
        self::assertSame([OnSale::class . '|on_sale', OnSale::class . '|discount_percent', OnSale::class . '|discount_amount'], array_column($byLabel['OpenLabel']['value'], 'value'));
        self::assertSame([Product::class . '|color'], array_column($byLabel['Product Attribute']['value'], 'value'));
        self::assertSame(Combine::class, $combine->getType());
    }

    public function testCollectsValidatedAttributesOfEveryChild(): void
    {
        $combine = $this->combine();
        $collection = $this->createStub(Collection::class);
        $child = $this->createMock(Product::class);
        $child->expects(self::once())->method('collectValidatedAttributes')->with($collection);
        $combine->addCondition($child);

        self::assertSame($combine, $combine->collectValidatedAttributes($collection));
    }

    public function testNestedCombinesAnswerGroupAndDateQuestions(): void
    {
        $outer = $this->combine();
        $inner = $this->combine();
        $inner->addCondition($this->objectManager->getObject(OnSale::class));
        $outer->addCondition($inner);

        self::assertTrue($outer->requiresCustomerGroup());
        self::assertFalse($outer->isDateRelative());
    }

    /**
     * @param string[] $excluded
     */
    private function combine(array $excluded = []): Combine
    {
        // The widget condition only reads getFrontendLabel() and getAttributeCode(): a DataObject stands in for the attribute.
        $color = new DataObject(['attribute_code' => 'color', 'frontend_label' => 'Color']);
        $resource = $this->createStub(ProductResource::class);
        $resource->method('loadAllAttributes')->willReturnSelf();
        $resource->method('getAttributesByCode')->willReturn(['color' => $color]);
        $product = $this->objectManager->getObject(Product::class, ['productResource' => $resource]);
        $productFactory = $this->createStub(ProductFactory::class);
        $productFactory->method('create')->willReturn($product);
        $pool = $this->createStub(ConditionPool::class);
        $pool->method('getConditions')->willReturn([$this->objectManager->getObject(OnSale::class)]);
        /** @var Combine $combine */
        $combine = $this->objectManager->getObject(Combine::class, [
            'productConditionFactory' => $productFactory,
            'conditionPool' => $pool,
            'excludedAttributes' => $excluded,
        ]);
        // The rule model sets the prefix in the application; children are stored under it (core Combine::addCondition).
        $combine->setPrefix('conditions');

        return $combine;
    }
}
