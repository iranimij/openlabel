<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Test\Unit\Model\Indexer;

use Iranimij\OpenLabel\Model\Indexer\Diff;
use PHPUnit\Framework\TestCase;

class DiffTest extends TestCase
{
    public function testAddedAndRemovedProductIds(): void
    {
        $diff = new Diff([1, 2, 3, 3], [3, 4]);

        self::assertSame([4], $diff->added);
        self::assertSame([1, 2], $diff->removed);
        self::assertSame([4, 1, 2], $diff->changed());
    }

    public function testIdenticalSetsAreNotAChange(): void
    {
        $diff = new Diff([2, 1], [1, 2]);

        self::assertSame([], $diff->changed());
        self::assertFalse($diff->isChanged());
    }
}
