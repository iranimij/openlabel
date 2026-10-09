<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Model\ResourceModel;

use Iranimij\OpenLabel\Api\Data\PlacementInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Placement extends AbstractDb
{
    public const TABLE = 'openlabel_placement';

    /**
     * @inheritDoc
     */
    protected function _construct(): void
    {
        $this->_init(self::TABLE, PlacementInterface::PLACEMENT_ID);
    }
}
