<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Model\Indexer;

use Iranimij\OpenLabel\Model\Cache\Invalidator;
use Iranimij\OpenLabel\Model\ResourceModel\Label\CollectionFactory as LabelCollectionFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Indexer\ActionInterface as IndexerActionInterface;
use Magento\Framework\Lock\LockManagerInterface;
use Magento\Framework\Mview\ActionInterface as MviewActionInterface;

/**
 * The openlabel_product indexer: full rebuild into the replica table with a swap (06 · F8), partial rebuilds for
 * changed products and their composite parents, cache cleaning bounded by what changed.
 */
class Product implements IndexerActionInterface, MviewActionInterface
{
    public const LOCK_NAME = 'openlabel_full_reindex';

    private const INDEX = 'openlabel_index';
    private const REPLICA = 'openlabel_index_replica';
    private const SWAP_TMP = 'openlabel_index_swap';

    /**
     * @param ResourceConnection $resource
     * @param LabelCollectionFactory $labelCollectionFactory
     * @param RowBuilder $rowBuilder
     * @param ParentRows $parentRows
     * @param Invalidator $invalidator
     * @param LockManagerInterface $lockManager
     */
    public function __construct(
        private readonly ResourceConnection $resource,
        private readonly LabelCollectionFactory $labelCollectionFactory,
        private readonly RowBuilder $rowBuilder,
        private readonly ParentRows $parentRows,
        private readonly Invalidator $invalidator,
        private readonly LockManagerInterface $lockManager
    ) {
    }

    /**
     * @inheritDoc
     * @throws LocalizedException when another full reindex is running
     */
    public function executeFull()
    {
        $this->acquireLock();
        try {
            $connection = $this->resource->getConnection();
            $replica = $this->resource->getTableName(self::REPLICA);
            $index = $this->resource->getTableName(self::INDEX);
            $connection->truncateTable($replica);
            foreach ($this->labelCollectionFactory->create()->getItems() as $label) {
                $this->rowBuilder->build($label, self::REPLICA);
            }
            $changed = array_merge($this->missingProducts($index, $replica), $this->missingProducts($replica, $index));
            $tmp = $this->resource->getTableName(self::SWAP_TMP);
            $connection->renameTablesBatch([
                ['oldName' => $index, 'newName' => $tmp],
                ['oldName' => $replica, 'newName' => $index],
                ['oldName' => $tmp, 'newName' => $replica],
            ]);
        } finally {
            $this->lockManager->unlock(self::LOCK_NAME);
        }
        $this->invalidator->clean($changed);
    }

    /**
     * @inheritDoc
     */
    public function executeList(array $ids)
    {
        $ids = array_values(array_unique(array_map('intval', $ids)));
        if ($ids === []) {
            return;
        }
        $parents = array_keys($this->parentRows->parentsOf($ids));
        $candidates = array_values(array_unique(array_merge($ids, $parents, $this->parentRows->childrenOf($parents))));
        $this->resource->getConnection()->delete(
            $this->resource->getTableName(self::INDEX),
            ['product_id IN (?)' => $candidates]
        );
        foreach ($this->labelCollectionFactory->create()->getItems() as $label) {
            $this->rowBuilder->build($label, self::INDEX, $candidates);
        }
        $this->invalidator->clean($candidates);
    }

    /**
     * @inheritDoc
     */
    public function executeRow($id)
    {
        $this->executeList([(int) $id]);
    }

    /**
     * @inheritDoc
     */
    public function execute($ids)
    {
        $this->executeList(is_array($ids) ? $ids : [(int) $ids]);
    }

    /**
     * @return void
     * @throws LocalizedException
     */
    private function acquireLock(): void
    {
        try {
            $acquired = !$this->lockManager->isLocked(self::LOCK_NAME) && $this->lockManager->lock(self::LOCK_NAME, 0);
        } catch (AlreadyExistsException $e) {
            $acquired = false;
        }
        if (!$acquired) {
            throw new LocalizedException(__('The OpenLabel full reindex is already running.'));
        }
    }

    /**
     * Product ids that have rows in one table but not the other (per label, store and group).
     *
     * @param string $inTable prefixed
     * @param string $notInTable prefixed
     * @return int[]
     */
    private function missingProducts(string $inTable, string $notInTable): array
    {
        $connection = $this->resource->getConnection();
        $select = $connection->select()
            ->distinct()
            ->from(['a' => $inTable], ['product_id'])
            ->joinLeft(
                ['b' => $notInTable],
                'b.label_id = a.label_id AND b.product_id = a.product_id AND b.store_id = a.store_id'
                . ' AND b.customer_group_id = a.customer_group_id',
                []
            )
            ->where('b.product_id IS NULL');

        return array_map('intval', $connection->fetchCol($select));
    }
}
