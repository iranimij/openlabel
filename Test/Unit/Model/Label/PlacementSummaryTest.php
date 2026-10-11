<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Test\Unit\Model\Label;

use Iranimij\OpenLabel\Model\Label\PlacementSummary;
use PHPUnit\Framework\TestCase;

class PlacementSummaryTest extends TestCase
{
    public function testFormatsAreaAndPositionInOrder(): void
    {
        self::assertSame('Listing TR, Product TL', (new PlacementSummary())->format('listing:tr,product:tl'));
    }

    public function testEmptyMeansNowhere(): void
    {
        self::assertSame('Nowhere yet', (new PlacementSummary())->format(null));
        self::assertSame('Nowhere yet', (new PlacementSummary())->format(''));
    }

    public function testUnknownAreaIsShownAsIs(): void
    {
        self::assertSame('Cart BR', (new PlacementSummary())->format('cart:br'));
    }
}
