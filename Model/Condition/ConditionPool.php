<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Model\Condition;

use Magento\Rule\Model\ConditionFactory;

/**
 * The built-in conditions offered in the tree. Extend through di.xml:
 * <type name="Iranimij\OpenLabel\Model\Condition\ConditionPool"><arguments><argument name="types" xsi:type="array">...
 *
 * @api
 */
class ConditionPool
{
    /**
     * @param ConditionFactory $conditionFactory
     * @param array<string, class-string<AbstractBuiltIn>> $types
     */
    public function __construct(
        private readonly ConditionFactory $conditionFactory,
        private readonly array $types = []
    ) {
    }

    /**
     * @return array<string, class-string<AbstractBuiltIn>>
     */
    public function getTypes(): array
    {
        return $this->types;
    }

    /**
     * Fresh instances, one per type, in configured order.
     *
     * @return AbstractBuiltIn[]
     */
    public function getConditions(): array
    {
        $conditions = [];
        foreach ($this->types as $type) {
            $condition = $this->conditionFactory->create($type);
            if ($condition instanceof AbstractBuiltIn) {
                $conditions[] = $condition;
            }
        }

        return $conditions;
    }
}
