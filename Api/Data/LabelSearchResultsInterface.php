<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * @api
 */
interface LabelSearchResultsInterface extends SearchResultsInterface
{
    /**
     * @return \Iranimij\OpenLabel\Api\Data\LabelInterface[]
     */
    public function getItems();

    /**
     * @param \Iranimij\OpenLabel\Api\Data\LabelInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
