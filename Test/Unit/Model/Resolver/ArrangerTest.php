<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Test\Unit\Model\Resolver;

use Iranimij\OpenLabel\Model\Resolver\Arranger;
use PHPUnit\Framework\TestCase;

/**
 * Priority, hide-lower-priority and max-per-stack rules on fixed index rows (02 · Architecture §4).
 */
class ArrangerTest extends TestCase
{
    public function testRowsAreOrderedByPriorityThenLabelIdPerProduct(): void
    {
        $rows = [
            $this->row(product: 1, label: 5, priority: 2),
            $this->row(product: 1, label: 3, priority: 0),
            $this->row(product: 2, label: 9, priority: 1),
            $this->row(product: 1, label: 4, priority: 0),
        ];

        $arranged = (new Arranger())->arrange($rows);

        self::assertSame([1, 2], array_keys($arranged));
        self::assertSame([3, 4, 5], array_column($arranged[1], 'label_id'));
        self::assertSame([9], array_column($arranged[2], 'label_id'));
    }

    public function testHideLowerPrioritySuppressesHigherNumbersOnThatProductOnly(): void
    {
        $rows = [
            $this->row(product: 1, label: 1, priority: 0),
            $this->row(product: 1, label: 2, priority: 1, hideLower: true),
            $this->row(product: 1, label: 3, priority: 2),
            $this->row(product: 1, label: 4, priority: 1, area: 'product'),
            $this->row(product: 2, label: 3, priority: 2),
        ];

        $arranged = (new Arranger())->arrange($rows);

        self::assertSame([1, 2, 4], array_column($arranged[1], 'label_id'), 'same priority stays, higher numbers go, in every area');
        self::assertSame([3], array_column($arranged[2], 'label_id'));
    }

    public function testMaxLabelsOfTheTopLabelLimitsEachStack(): void
    {
        $rows = [
            $this->row(product: 1, label: 1, priority: 0, maxLabels: 2),
            $this->row(product: 1, label: 2, priority: 1, maxLabels: 5),
            $this->row(product: 1, label: 3, priority: 2, maxLabels: 5),
            $this->row(product: 1, label: 4, priority: 3, position: 'br', maxLabels: 1),
            $this->row(product: 1, label: 5, priority: 4, position: 'br'),
            $this->row(product: 1, label: 6, priority: 0, area: 'product', maxLabels: 1),
            $this->row(product: 1, label: 7, priority: 1, area: 'product'),
        ];

        $arranged = (new Arranger())->arrange($rows);

        self::assertSame([1, 6, 2, 4], array_column($arranged[1], 'label_id'), 'priority order across areas; tl keeps 2 (limit of label 1), br keeps 1, product/tl keeps 1');
    }

    public function testEmptyInputGivesEmptyOutput(): void
    {
        self::assertSame([], (new Arranger())->arrange([]));
    }

    /**
     * @return array<string, mixed>
     */
    private function row(
        int $product,
        int $label,
        int $priority,
        bool $hideLower = false,
        string $area = 'listing',
        string $position = 'tl',
        int $maxLabels = 3
    ): array {
        return [
            'product_id' => $product,
            'label_id' => $label,
            'priority' => $priority,
            'hide_lower_priority' => (int) $hideLower,
            'area' => $area,
            'position' => $position,
            'max_labels' => $maxLabels,
        ];
    }
}
