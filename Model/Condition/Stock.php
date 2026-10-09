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
     * @inheritDoc
     */
    public function getMappedSqlField()
    {
        $stock = $this->stockDataResolver->get();
        $column = (string) $this->getAttribute() === self::SALABLE_QTY ? $stock->getQtyColumn() : $stock->getSalableColumn();

        return self::ALIAS . '.' . $column;
    }

    /**
     * @inheritDoc
     */
    protected function productValue(Product $product): mixed
    {
        $stock = $this->stockDataResolver->get();
        if ((string) $this->getAttribute() === self::SALABLE_QTY) {
            return $stock->getSalableQty($product, $this->websiteId());
        }

        return $stock->isSalable($product, $this->websiteId()) ? 1 : 0;
    }
}
