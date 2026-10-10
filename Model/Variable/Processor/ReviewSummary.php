<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Model\Variable\Processor;

use Iranimij\OpenLabel\Api\VariablePreloadInterface;
use Iranimij\OpenLabel\Model\Variable\Context;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DataObject;

/**
 * Shared by {RATING} and {REVIEW_COUNT}: reads the summary already on the product (listing collections carry
 * rating_summary and reviews_count; the product page carries a summary object) or preloads it for many products.
 */
class ReviewSummary implements VariablePreloadInterface
{
    private const ENTITY_TYPE_PRODUCT = 1;

    /** @var array<int, array<int, array{rating_summary: int|null, reviews_count: int|null}>> store => product => data */
    private array $preloaded = [];

    /**
     * @param ResourceConnection $resource
     */
    public function __construct(private readonly ResourceConnection $resource)
    {
    }

    /**
     * @inheritDoc
     */
    public function preload(array $products, Context $context): void
    {
        $ids = [];
        foreach ($products as $product) {
            if ($product->getId() && (!$product instanceof Product || $product->getData('rating_summary') === null)) {
                $ids[] = (int) $product->getId();
            }
        }
        if ($ids === []) {
            return;
        }
        $connection = $this->resource->getConnection();
        $select = $connection->select()
            ->from($this->resource->getTableName('review_entity_summary'), ['entity_pk_value', 'rating_summary', 'reviews_count'])
            ->where('entity_type = ?', self::ENTITY_TYPE_PRODUCT)
            ->where('store_id = ?', $context->storeId)
            ->where('entity_pk_value IN (?)', $ids, \Zend_Db::INT_TYPE);
        foreach ($ids as $id) {
            $this->preloaded[$context->storeId][$id] = ['rating_summary' => null, 'reviews_count' => null];
        }
        foreach ($connection->fetchAll($select) as $row) {
            $this->preloaded[$context->storeId][(int) $row['entity_pk_value']] = [
                'rating_summary' => (int) $row['rating_summary'],
                'reviews_count' => (int) $row['reviews_count'],
            ];
        }
    }

    /**
     * @param ProductInterface $product
     * @param Context $context
     * @param string $field rating_summary or reviews_count
     * @return int|null
     */
    public function read(ProductInterface $product, Context $context, string $field): ?int
    {
        $summary = $product instanceof Product ? $product->getData('rating_summary') : null;
        if ($summary instanceof DataObject) {
            $value = $summary->getData($field);
        } elseif ($field === 'rating_summary') {
            $value = $summary;
        } else {
            $value = $product instanceof Product ? $product->getData('reviews_count') : null;
        }
        if (($value === null || $value === '') && $product->getId()) {
            $value = $this->preloaded[$context->storeId][(int) $product->getId()][$field] ?? null;
        }

        return $value === null || $value === '' ? null : (int) $value;
    }
}
