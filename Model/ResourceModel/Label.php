<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Model\ResourceModel;

use Iranimij\OpenLabel\Api\Data\LabelInterface;
use Iranimij\OpenLabel\Api\Data\PlacementInterface;
use Iranimij\OpenLabel\Model\Label as LabelModel;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Iranimij\OpenLabel\Model\PlacementFactory;
use Iranimij\OpenLabel\Model\ResourceModel\Placement as PlacementResource;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;

class Label extends AbstractDb
{
    public const TABLE = 'openlabel_label';

    /**
     * @param Context $context
     * @param PlacementFactory $placementFactory
     * @param string|null $connectionName
     */
    public function __construct(
        Context $context,
        private readonly PlacementFactory $placementFactory,
        ?string $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
    }

    /**
     * @inheritDoc
     */
    protected function _construct(): void
    {
        $this->_init(self::TABLE, LabelInterface::LABEL_ID);
    }

    /**
     * @inheritDoc
     */
    protected function _beforeSave(AbstractModel $object): AbstractDb
    {
        foreach ([LabelInterface::STORE_IDS, LabelInterface::CUSTOMER_GROUP_IDS] as $field) {
            $value = $object->getData($field);
            if (is_array($value)) {
                $object->setData($field, implode(',', array_map('intval', $value)));
            }
        }
        foreach ([LabelInterface::VALID_FROM, LabelInterface::VALID_TO, LabelInterface::CONDITIONS_SERIALIZED] as $field) {
            if ($object->getData($field) === '') {
                $object->setData($field, null);
            }
        }

        return parent::_beforeSave($object);
    }

    /**
     * Load the placements of one label (ordered by sort_order) into the model.
     *
     * @param LabelModel $label
     * @return void
     */
    public function loadPlacements(LabelModel $label): void
    {
        $this->loadPlacementsFor([$label]);
    }

    /**
     * Load placements for many labels with one query.
     *
     * @param LabelModel[] $labels
     * @return void
     */
    public function loadPlacementsFor(array $labels): void
    {
        $byId = [];
        foreach ($labels as $label) {
            $label->setPlacements([]);
            if ($label->getLabelId() !== null) {
                $byId[$label->getLabelId()] = $label;
            }
        }
        if ($byId === []) {
            return;
        }
        $connection = $this->connection();
        $select = $connection->select()
            ->from($this->getTable(PlacementResource::TABLE))
            ->where(PlacementInterface::LABEL_ID . ' IN (?)', array_keys($byId), \Zend_Db::INT_TYPE)
            ->order([PlacementInterface::SORT_ORDER . ' ASC', PlacementInterface::PLACEMENT_ID . ' ASC']);
        $grouped = [];
        foreach ($connection->fetchAll($select) as $row) {
            $placement = $this->placementFactory->create();
            $placement->setData($row);
            $grouped[(int) $row[PlacementInterface::LABEL_ID]][] = $placement;
        }
        foreach ($grouped as $labelId => $placements) {
            $byId[$labelId]->setPlacements($placements);
        }
    }

    /**
     * Replace the stored placements of a label with the ones on the model.
     *
     * @param LabelModel $label
     * @return void
     */
    public function savePlacements(LabelModel $label): void
    {
        $connection = $this->connection();
        $table = $this->getTable(PlacementResource::TABLE);
        $labelId = (int) $label->getLabelId();
        $connection->delete($table, [PlacementInterface::LABEL_ID . ' = ?' => $labelId]);
        $sortOrder = 0;
        foreach ($label->getPlacements() as $placement) {
            $connection->insert($table, [
                PlacementInterface::LABEL_ID => $labelId,
                PlacementInterface::AREA => $placement->getArea(),
                PlacementInterface::POSITION => $placement->getPosition(),
                PlacementInterface::PIN_PHYSICAL_SIDE => (int) $placement->isPinPhysicalSide(),
                PlacementInterface::DESIGN_ID => $placement->getDesignId(),
                PlacementInterface::OFFSET_X => $placement->getOffsetX(),
                PlacementInterface::OFFSET_Y => $placement->getOffsetY(),
                PlacementInterface::MAX_LABELS => $placement->getMaxLabels(),
                PlacementInterface::STACKING => $placement->getStacking(),
                PlacementInterface::GAP => $placement->getGap(),
                PlacementInterface::SORT_ORDER => $sortOrder++,
            ]);
        }
        $this->loadPlacementsFor([$label]);
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
