<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Test\Unit\Model\Condition;

use Iranimij\OpenLabel\Model\Condition\AbstractBuiltIn;
use Iranimij\OpenLabel\Model\Rule\Rule;
use Magento\Catalog\Model\Product;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * Shared helpers: a condition wired to a rule with store 1, website 1, customer group 2, and stub products.
 */
abstract class ConditionTestCase extends TestCase
{
    protected ObjectManager $objectManager;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
    }

    /**
     * @param class-string<AbstractBuiltIn> $class
     * @param array<string, mixed> $arguments extra constructor arguments
     */
    protected function condition(string $class, string $attribute, string $operator, mixed $value, array $arguments = []): AbstractBuiltIn
    {
        /** @var AbstractBuiltIn $condition */
        $condition = $this->objectManager->getObject($class, $arguments);
        $rule = $this->createStub(Rule::class);
        $rule->method('getStoreId')->willReturn(1);
        $rule->method('getWebsiteId')->willReturn(1);
        $rule->method('getCustomerGroupId')->willReturn(2);
        $condition->setRule($rule);
        $condition->setAttribute($attribute)->setOperator($operator)->setValue($value);

        return $condition;
    }

    /**
     * @param array<string, mixed> $data
     */
    protected function product(array $data): Product
    {
        /** @var Product $product */
        $product = $this->objectManager->getObject(Product::class);
        $product->setData($data);

        return $product;
    }
}
