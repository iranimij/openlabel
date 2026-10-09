<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Model\Variable;

/**
 * Where a label is rendered: store view, website and the visitor's customer group.
 */
class Context
{
    /**
     * @param int $storeId
     * @param int $websiteId
     * @param int $customerGroupId
     */
    public function __construct(
        public readonly int $storeId,
        public readonly int $websiteId,
        public readonly int $customerGroupId
    ) {
    }
}
