<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Model\Variable\Processor;

use Iranimij\OpenLabel\Api\VariablePreloadInterface;
use Iranimij\OpenLabel\Api\VariableProcessorInterface;
use Iranimij\OpenLabel\Model\Variable\Context;
use Iranimij\OpenLabel\Model\Variable\Value;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Stdlib\DateTime\DateTime;

/**
 * {SOLD_LAST_30D}: quantity ordered in the last 30 days, from a bounded aggregation over sales_order_item (06 · F26),
 * loaded for a whole listing at once.
 */
class SoldLast30d implements VariableProcessorInterface, VariablePreloadInterface
{
    private const DAYS = 30;

    /** @var array<int, int> product id => quantity */
    private array $sold = [];

    /**
     * @param ResourceConnection $resource
     * @param DateTime $dateTime
     */
    public function __construct(
        private readonly ResourceConnection $resource,
        private readonly DateTime $dateTime
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getCode(): string
    {
        return 'SOLD_LAST_30D';
    }

    /**
     * @inheritDoc
     */
    public function preload(array $products, Context $context): void
    {
        $ids = [];
        foreach ($products as $product) {
            if ($product->getId() && !isset($this->sold[(int) $product->getId()])) {
                $ids[] = (int) $product->getId();
            }
        }
        if ($ids === []) {
            return;
        }
        $this->load($ids);
    }

    /**
     * @inheritDoc
     */
    public function getValue(ProductInterface $product, Context $context, string $argument = ''): Value
    {
        $id = (int) $product->getId();
        if ($id === 0) {
            return Value::empty();
        }
        if (!isset($this->sold[$id])) {
            $this->load([$id]);
        }

        return Value::number($this->sold[$id]);
    }

    /**
     * @param int[] $ids
     * @return void
     */
    private function load(array $ids): void
    {
        foreach ($ids as $id) {
            $this->sold[$id] = 0;
        }
        $connection = $this->resource->getConnection();
        $since = $this->dateTime->gmtDate(null, $this->dateTime->gmtTimestamp() - self::DAYS * 86400);
        $select = $connection->select()
            ->from($this->resource->getTableName('sales_order_item'), ['product_id', 'qty' => 'SUM(qty_ordered)'])
            ->where('product_id IN (?)', $ids, \Zend_Db::INT_TYPE)
            ->where('created_at >= ?', $since)
            ->group('product_id');
        foreach ($connection->fetchAll($select) as $row) {
            $this->sold[(int) $row['product_id']] = (int) round((float) $row['qty']);
        }
    }
}
