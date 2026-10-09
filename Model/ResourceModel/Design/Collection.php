<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Model\ResourceModel\Design;

use Iranimij\OpenLabel\Model\Design;
use Iranimij\OpenLabel\Model\ResourceModel\Design as DesignResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * @method Design[] getItems()
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'design_id';

    /**
     * @var string
     */
    protected $_eventPrefix = 'openlabel_design_collection';

    /**
     * @inheritDoc
     */
    protected function _construct(): void
    {
        $this->_init(Design::class, DesignResource::class);
    }

    /**
     * @inheritDoc
     */
    protected function _afterLoad(): AbstractCollection
    {
        /** @var DesignResource $resource */
        $resource = $this->getResource();
        $resource->loadStoreTextsFor($this->getItems());

        return parent::_afterLoad();
    }
}
