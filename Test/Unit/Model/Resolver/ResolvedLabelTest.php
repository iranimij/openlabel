<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Test\Unit\Model\Resolver;

use Iranimij\OpenLabel\Model\Design;
use Iranimij\OpenLabel\Model\Placement;
use Iranimij\OpenLabel\Model\Resolver\ResolvedLabel;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

class ResolvedLabelTest extends TestCase
{
    public function testExposesEveryPart(): void
    {
        $objectManager = new ObjectManager($this);
        /** @var Placement $placement */
        $placement = $objectManager->getObject(Placement::class);
        /** @var Design $design */
        $design = $objectManager->getObject(Design::class);
        $design->setText('Sale')->setAltText('Sale badge');

        $label = new ResolvedLabel(3, 'Sale', 1, true, 7, 9, $placement, $design);

        self::assertSame(3, $label->getLabelId());
        self::assertSame('Sale', $label->getName());
        self::assertSame(1, $label->getPriority());
        self::assertTrue($label->isHideLowerPriority());
        self::assertSame(7, $label->getProductId());
        self::assertSame(9, $label->getParentProductId());
        self::assertSame($placement, $label->getPlacement());
        self::assertSame($design, $label->getDesign());
        self::assertSame('Sale', $label->getText());
        self::assertSame('Sale badge', $label->getAltText());
    }
}
