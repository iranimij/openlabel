<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Model\Indexer;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\MetadataPool;

/**
 * Parent/child relations of configurable, grouped and bundle products from catalog_product_relation,
 * resolved through the entity link field so it works on Adobe Commerce staging too.
 */
class ParentRows
{
    /**
     * @param ResourceConnection $resource
     * @param MetadataPool $metadataPool
     */
    public function __construct(
        private readonly ResourceConnection $resource,
        private readonly MetadataPool $metadataPool
    ) {
    }

    /**
     * @param int[] $childIds
     * @return array<int, int[]> parent entity id => child entity ids among $childIds
     */
    public function parentsOf(array $childIds): array
    {
        if ($childIds === []) {
            return [];
        }
        $connection = $this->resource->getConnection();
        $select = $connection->select()
            ->from(['r' => $this->resource->getTableName('catalog_product_relation')], ['child_id'])
            ->join(['p' => $this->resource->getTableName('catalog_product_entity')], 'p.' . $this->linkField() . ' = r.parent_id', ['entity_id'])
            ->where('r.child_id IN (?)', array_map('intval', $childIds), \Zend_Db::INT_TYPE);
        $parents = [];
        foreach ($connection->fetchAll($select) as $row) {
            $parents[(int) $row['entity_id']][] = (int) $row['child_id'];
        }
        ksort($parents);

        return $parents;
    }

    /**
     * @param int[] $parentIds
     * @return int[] child entity ids
     */
    public function childrenOf(array $parentIds): array
    {
        if ($parentIds === []) {
            return [];
        }
        $connection = $this->resource->getConnection();
        $select = $connection->select()
            ->from(['r' => $this->resource->getTableName('catalog_product_relation')], ['child_id'])
            ->join(['p' => $this->resource->getTableName('catalog_product_entity')], 'p.' . $this->linkField() . ' = r.parent_id', [])
            ->where('p.entity_id IN (?)', array_map('intval', $parentIds), \Zend_Db::INT_TYPE);

        return array_values(array_unique(array_map('intval', $connection->fetchCol($select))));
    }

    /**
     * @return string
     */
    private function linkField(): string
    {
        return (string) $this->metadataPool->getMetadata(ProductInterface::class)->getLinkField();
    }
}
