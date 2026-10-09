<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Model\Condition;

/**
 * The review summary join shared by Rating and ReviewCount (same alias, joined once).
 */
trait ReviewSummaryJoin
{
    public const REVIEW_ALIAS = 'ol_review';

    /** Product reviews: review_entity.entity_code = 'product'. */
    private const ENTITY_TYPE_PRODUCT = 1;

    /**
     * @return array<string, array<string, mixed>>
     */
    public function getTablesToJoin()
    {
        return [
            self::REVIEW_ALIAS => [
                'name' => 'review_entity_summary',
                'condition' => sprintf(
                    '%1$s.entity_pk_value = e.entity_id AND %1$s.entity_type = %2$d AND %1$s.store_id = %3$d',
                    self::REVIEW_ALIAS,
                    self::ENTITY_TYPE_PRODUCT,
                    $this->storeId()
                ),
                'columns' => [],
            ],
        ];
    }
}
