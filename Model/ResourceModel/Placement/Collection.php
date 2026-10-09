<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Model\ResourceModel\Placement;

use Iranimij\OpenLabel\Model\Placement;
use Iranimij\OpenLabel\Model\ResourceModel\Placement as PlacementResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * @method Placement[] getItems()
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'placement_id';

    /**
     * @inheritDoc
     */
    protected function _construct(): void
    {
        $this->_init(Placement::class, PlacementResource::class);
    }
}
