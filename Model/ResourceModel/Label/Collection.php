<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Model\ResourceModel\Label;

use Iranimij\OpenLabel\Model\Label;
use Iranimij\OpenLabel\Model\ResourceModel\Label as LabelResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * @method Label[] getItems()
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'label_id';

    /**
     * @var string
     */
    protected $_eventPrefix = 'openlabel_label_collection';

    /**
     * @inheritDoc
     */
    protected function _construct(): void
    {
        $this->_init(Label::class, LabelResource::class);
    }

    /**
     * Load placements for every label in the collection with one extra query.
     *
     * @return $this
     */
    public function addPlacements(): self
    {
        $this->load();
        /** @var LabelResource $resource */
        $resource = $this->getResource();
        $resource->loadPlacementsFor($this->getItems());

        return $this;
    }
}
