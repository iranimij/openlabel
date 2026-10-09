<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Model\Resolver;

/**
 * Orders index rows per product by priority, applies "hide lower priority" and the max-per-stack limit.
 * A stack is one area + position; its limit is the max_labels of its highest-priority label.
 */
class Arranger
{
    /**
     * @param array<int, array<string, mixed>> $rows with product_id, label_id, priority, hide_lower_priority,
     *        area, position, max_labels
     * @return array<int, array<int, array<string, mixed>>> product id => rows in display order
     */
    public function arrange(array $rows): array
    {
        $byProduct = [];
        foreach ($rows as $row) {
            $byProduct[(int) $row['product_id']][] = $row;
        }
        $result = [];
        foreach ($byProduct as $productId => $productRows) {
            usort($productRows, static fn (array $a, array $b): int =>
                [(int) $a['priority'], (int) $a['label_id']] <=> [(int) $b['priority'], (int) $b['label_id']]);
            $cutoff = null;
            foreach ($productRows as $row) {
                if ((int) $row['hide_lower_priority'] === 1) {
                    $cutoff = $cutoff === null ? (int) $row['priority'] : min($cutoff, (int) $row['priority']);
                }
            }
            $stacks = [];
            $kept = [];
            foreach ($productRows as $row) {
                if ($cutoff !== null && (int) $row['priority'] > $cutoff) {
                    continue;
                }
                $stack = $row['area'] . '/' . $row['position'];
                if (!isset($stacks[$stack])) {
                    $stacks[$stack] = ['limit' => max(1, (int) $row['max_labels']), 'count' => 0];
                }
                if ($stacks[$stack]['count'] >= $stacks[$stack]['limit']) {
                    continue;
                }
                $stacks[$stack]['count']++;
                $kept[] = $row;
            }
            $result[$productId] = $kept;
        }

        return $result;
    }
}
