<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Model\Rule\Condition;

use Iranimij\OpenLabel\Model\Condition\AbstractBuiltIn;
use Iranimij\OpenLabel\Model\Condition\ConditionPool;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Rule\Model\Condition\Combine as CoreCombine;
use Magento\Rule\Model\Condition\Context;

/**
 * The ALL/ANY node of a label's condition tree: native product attributes plus the OpenLabel built-ins.
 *
 * @method $this setType(string $type)
 * @method string|null getType()
 * @method $this setPrefix(string $prefix)
 * @method string|null getPrefix()
 */
class Combine extends CoreCombine
{
    /**
     * @param Context $context
     * @param ProductFactory $productConditionFactory
     * @param ConditionPool $conditionPool
     * @param array<string, mixed> $data
     * @param string[] $excludedAttributes attribute codes hidden from the tree
     */
    public function __construct(
        Context $context,
        private readonly ProductFactory $productConditionFactory,
        private readonly ConditionPool $conditionPool,
        array $data = [],
        private readonly array $excludedAttributes = []
    ) {
        parent::__construct($context, $data);
        $this->setType(self::class);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getNewChildSelectOptions()
    {
        $attributes = [];
        $options = $this->productConditionFactory->create()->loadAttributeOptions()->getAttributeOption();
        foreach ($options as $code => $label) {
            if (!in_array($code, $this->excludedAttributes, true)) {
                $attributes[] = ['value' => Product::class . '|' . $code, 'label' => $label];
            }
        }
        $builtIns = [];
        foreach ($this->conditionPool->getConditions() as $condition) {
            foreach ($condition->loadAttributeOptions()->getAttributeOption() as $code => $label) {
                $builtIns[] = ['value' => $condition::class . '|' . $code, 'label' => $label];
            }
        }
        $conditions = parent::getNewChildSelectOptions();

        return array_merge_recursive($conditions, [
            ['value' => self::class, 'label' => __('Conditions Combination')],
            ['label' => __('OpenLabel'), 'value' => $builtIns],
            ['label' => __('Product Attribute'), 'value' => $attributes],
        ]);
    }

    /**
     * Let every leaf join what it needs before the SQL builder attaches the WHERE clause.
     *
     * @param ProductCollection $productCollection
     * @return $this
     */
    public function collectValidatedAttributes(ProductCollection $productCollection): self
    {
        foreach ($this->getConditions() as $condition) {
            $condition->collectValidatedAttributes($productCollection);
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function requiresCustomerGroup(): bool
    {
        foreach ($this->getConditions() as $condition) {
            if (($condition instanceof self || $condition instanceof AbstractBuiltIn) && $condition->requiresCustomerGroup()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return bool
     */
    public function isDateRelative(): bool
    {
        foreach ($this->getConditions() as $condition) {
            if (($condition instanceof self || $condition instanceof AbstractBuiltIn) && $condition->isDateRelative()) {
                return true;
            }
        }

        return false;
    }
}
