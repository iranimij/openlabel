<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Model\Indexer;

use Iranimij\OpenLabel\Api\Data\LabelInterface;
use Iranimij\OpenLabel\Api\LabelRepositoryInterface;
use Iranimij\OpenLabel\Model\Cache\Invalidator;
use Magento\Framework\App\ResourceConnection;

/**
 * Rebuilds the rows of one label (on label save, CLI and the daily cron) and cleans the cache for the diff.
 */
class LabelReindexer
{
    public const INDEX_TABLE = 'openlabel_index';

    /**
     * @param ResourceConnection $resource
     * @param LabelRepositoryInterface $labelRepository
     * @param RowBuilder $rowBuilder
     * @param Invalidator $invalidator
     */
    public function __construct(
        private readonly ResourceConnection $resource,
        private readonly LabelRepositoryInterface $labelRepository,
        private readonly RowBuilder $rowBuilder,
        private readonly Invalidator $invalidator
    ) {
    }

    /**
     * @param int $labelId
     * @return Diff product ids that gained or lost the label
     */
    public function reindexLabel(int $labelId): Diff
    {
        $label = $this->labelRepository->getById($labelId);
        $before = $this->productIds($labelId);
        $this->deleteRows($labelId);
        $this->rowBuilder->build($label, self::INDEX_TABLE);
        $diff = new Diff($before, $this->productIds($labelId));
        $this->invalidator->clean($diff->changed(), [$labelId]);

        return $diff;
    }

    /**
     * @param LabelInterface $label
     * @return Diff
     */
    public function reindex(LabelInterface $label): Diff
    {
        return $this->reindexLabel((int) $label->getLabelId());
    }

    /**
     * Drop the rows of a deleted label and clean the products that showed it.
     *
     * @param int $labelId
     * @return int[] product ids that lost the label
     */
    public function removeLabel(int $labelId): array
    {
        $removed = $this->productIds($labelId);
        $this->deleteRows($labelId);
        $this->invalidator->clean($removed, [$labelId]);

        return $removed;
    }

    /**
     * @param int $labelId
     * @return int[]
     */
    public function productIds(int $labelId): array
    {
        $connection = $this->resource->getConnection();
        $select = $connection->select()
            ->distinct()
            ->from($this->resource->getTableName(self::INDEX_TABLE), ['product_id'])
            ->where('label_id = ?', $labelId);

        return array_map('intval', $connection->fetchCol($select));
    }

    /**
     * @param int $labelId
     * @return void
     */
    private function deleteRows(int $labelId): void
    {
        $this->resource->getConnection()->delete($this->resource->getTableName(self::INDEX_TABLE), ['label_id = ?' => $labelId]);
    }
}
