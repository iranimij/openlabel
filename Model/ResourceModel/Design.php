<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Model\ResourceModel;

use Iranimij\OpenLabel\Api\Data\DesignInterface;
use Iranimij\OpenLabel\Model\Design as DesignModel;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Design extends AbstractDb
{
    public const TABLE = 'openlabel_design';
    public const STORE_TABLE = 'openlabel_design_store';

    /**
     * @inheritDoc
     */
    protected function _construct(): void
    {
        $this->_init(self::TABLE, DesignInterface::DESIGN_ID);
    }

    /**
     * @inheritDoc
     */
    protected function _afterLoad(AbstractModel $object): AbstractDb
    {
        if ($object instanceof DesignModel) {
            $this->loadStoreTextsFor([$object]);
        }

        return parent::_afterLoad($object);
    }

    /**
     * @inheritDoc
     */
    protected function _afterSave(AbstractModel $object): AbstractDb
    {
        if ($object instanceof DesignModel && $object->hasData(DesignInterface::STORE_TEXTS)) {
            $this->saveStoreTexts($object);
        }

        return parent::_afterSave($object);
    }

    /**
     * Load the store-scoped rows for many designs with one query.
     *
     * @param DesignModel[] $designs
     * @return void
     */
    public function loadStoreTextsFor(array $designs): void
    {
        $byId = [];
        foreach ($designs as $design) {
            $design->setStoreTexts([]);
            if ($design->getDesignId() !== null) {
                $byId[$design->getDesignId()] = $design;
            }
        }
        if ($byId === []) {
            return;
        }
        $connection = $this->connection();
        $select = $connection->select()
            ->from($this->getTable(self::STORE_TABLE))
            ->where(DesignInterface::DESIGN_ID . ' IN (?)', array_keys($byId), \Zend_Db::INT_TYPE);
        $grouped = [];
        foreach ($connection->fetchAll($select) as $row) {
            $grouped[(int) $row['design_id']][(int) $row['store_id']] = [
                'text' => $row['text'],
                'alt_text' => $row['alt_text'],
                'tooltip' => $row['tooltip'],
            ];
        }
        foreach ($grouped as $designId => $rows) {
            $byId[$designId]->setStoreTexts($rows);
        }
    }

    /**
     * Replace the store-scoped rows of a design with the ones on the model.
     *
     * @param DesignModel $design
     * @return void
     */
    private function saveStoreTexts(DesignModel $design): void
    {
        $connection = $this->connection();
        $table = $this->getTable(self::STORE_TABLE);
        $designId = (int) $design->getDesignId();
        $connection->delete($table, [DesignInterface::DESIGN_ID . ' = ?' => $designId]);
        $rows = [];
        foreach ($design->getStoreTexts() as $storeId => $row) {
            if ($row['text'] === null && $row['alt_text'] === null && $row['tooltip'] === null) {
                continue;
            }
            $rows[] = [
                'design_id' => $designId,
                'store_id' => (int) $storeId,
                'text' => $row['text'],
                'alt_text' => $row['alt_text'],
                'tooltip' => $row['tooltip'],
            ];
        }
        if ($rows !== []) {
            $connection->insertMultiple($table, $rows);
        }
    }

    /**
     * @return AdapterInterface
     */
    private function connection(): AdapterInterface
    {
        $connection = $this->getConnection();
        if ($connection === false) {
            throw new \RuntimeException('No database connection for ' . self::TABLE);
        }

        return $connection;
    }
}
