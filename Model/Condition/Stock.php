<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Model\Condition;

use Iranimij\OpenLabel\Model\Condition\Stock\StockDataResolver;
use Magento\Catalog\Model\Product;
use Magento\Rule\Model\Condition\Context;

/**
 * Stock: salable (MSI-aware, per website stock) and salable quantity, with the legacy fallback (06 · F6).
 */
class Stock extends AbstractBuiltIn
{
    public const IS_SALABLE = 'is_salable';
    public const SALABLE_QTY = 'salable_qty';

    private const ALIAS = 'ol_stock';

    /** Product types that carry a quantity of their own; composites get quantity labels through apply_to_parent. */
    private const QUANTITY_TYPES = ['simple', 'virtual', 'downloadable'];

    /**
     * @param Context $context
     * @param StockDataResolver $stockDataResolver
     * @param array<string, mixed> $data
     */
    public function __construct(
        Context $context,
        private readonly StockDataResolver $stockDataResolver,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * @inheritDoc
     */
    protected function attributeOptions(): array
    {
        return [
            self::IS_SALABLE => __('In stock (salable)'),
            self::SALABLE_QTY => __('Salable quantity'),
        ];
    }

    /**
     * @inheritDoc
     */
    protected function booleanAttributes(): array
    {
        return [self::IS_SALABLE];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function getTablesToJoin()
    {
        $stock = $this->stockDataResolver->get();

        return [
            self::ALIAS => [
                'name' => $stock->getTableName($this->websiteId()),
                'condition' => $stock->getJoinCondition(self::ALIAS, $this->websiteId()),
                'columns' => [],
            ],
        ];
    }

    /**
     * Quantity comparisons apply to products with a quantity of their own; composites yield NULL and never match.
     *
     * @return \Zend_Db_Expr|string
     */
    public function getMappedSqlField()
    {
        $stock = $this->stockDataResolver->get();
        if ((string) $this->getAttribute() !== self::SALABLE_QTY) {
            return self::ALIAS . '.' . $stock->getSalableColumn();
        }
        $types = "'" . implode("','", self::QUANTITY_TYPES) . "'";

        return new \Zend_Db_Expr(sprintf(
            'IF(e.type_id IN (%s), %s.%s, %s)',
            $types,
            self::ALIAS,
            $stock->getQtyColumn(),
            $this->compositeSentinel()
        ));
    }

    /**
     * A value for composite products that never satisfies the comparison. NULL is not enough: from 2.4.8 the core
     * Sql Builder wraps numeric fields in IFNULL(field, 0), which would turn composites into "quantity 0".
     *
     * @return string
     */
    private function compositeSentinel(): string
    {
        $operator = (string) $this->getOperator();
        if (in_array($operator, ['<', '<='], true)) {
            return '1000000000000';
        }
        $value = $this->getValue();
        if (in_array($operator, ['!=', '!()'], true) && is_numeric($value)) {
            return (string) (float) $value;
        }

        return '-1';
    }

    /**
     * @inheritDoc
     */
    protected function productValue(Product $product): mixed
    {
        $stock = $this->stockDataResolver->get();
        if ((string) $this->getAttribute() === self::SALABLE_QTY) {
            $type = (string) $product->getTypeId();
            if ($type !== '' && !in_array($type, self::QUANTITY_TYPES, true)) {
                return null;
            }

            return $stock->getSalableQty($product, $this->websiteId());
        }

        return $stock->isSalable($product, $this->websiteId()) ? 1 : 0;
    }
}
