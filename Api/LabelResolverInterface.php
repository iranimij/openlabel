<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Api;

use Iranimij\OpenLabel\Api\Data\ResolvedLabelInterface;

/**
 * Which labels to show on which products, from the index, in one query per call (02 · Architecture §4).
 *
 * @api
 */
interface LabelResolverInterface
{
    /**
     * Labels per product, in priority order, with time window, status, customer group, max-per-stack and
     * hide-lower-priority already applied. Products without labels are absent from the result.
     *
     * @param int[] $productIds
     * @param int $storeId
     * @param int $customerGroupId
     * @return array<int, ResolvedLabelInterface[]> product id => labels
     */
    public function getForProducts(array $productIds, int $storeId, int $customerGroupId): array;
}
