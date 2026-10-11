<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Test\Unit\Model\Source;

use Iranimij\OpenLabel\Api\Data\PlacementInterface;
use Iranimij\OpenLabel\Model\Source\Position;
use Iranimij\OpenLabel\Model\Source\Stacking;
use PHPUnit\Framework\TestCase;

class PlacementOptionsTest extends TestCase
{
    public function testPositionsFollowTheImageGridRowByRow(): void
    {
        $values = array_column((new Position())->toOptionArray(), 'value');

        self::assertSame(PlacementInterface::POSITIONS, $values, 'the 3×3 picker lays them out in this order');
    }

    public function testStackingOffersBothDirections(): void
    {
        self::assertSame(
            [PlacementInterface::STACKING_VERTICAL, PlacementInterface::STACKING_HORIZONTAL],
            array_column((new Stacking())->toOptionArray(), 'value')
        );
    }
}
