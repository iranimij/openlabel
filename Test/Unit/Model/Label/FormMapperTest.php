<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Test\Unit\Model\Label;

use Iranimij\OpenLabel\Api\Data\PlacementInterfaceFactory;
use Iranimij\OpenLabel\Model\Label;
use Iranimij\OpenLabel\Model\Label\DateConverter;
use Iranimij\OpenLabel\Model\Label\FormMapper;
use Iranimij\OpenLabel\Model\Placement;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

class FormMapperTest extends TestCase
{
    private ObjectManager $objectManager;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
    }

    private function mapper(): FormMapper
    {
        $tz = $this->createMock(TimezoneInterface::class);
        $tz->method('getConfigTimezone')->willReturn('Europe/Berlin');
        $locale = $this->createMock(ResolverInterface::class);
        $locale->method('getLocale')->willReturn('en_US');
        $placements = $this->createMock(PlacementInterfaceFactory::class);
        $placements->method('create')->willReturnCallback(fn () => $this->objectManager->getObject(Placement::class));

        return new FormMapper(new DateConverter($tz, $locale), $placements);
    }

    private function label(): Label
    {
        return $this->objectManager->getObject(Label::class);
    }

    public function testBasicsScopeAndScheduleAreMapped(): void
    {
        $label = $this->label();

        $this->mapper()->apply($label, [
            'name' => '  Black Friday ', 'status' => '1', 'priority' => '3', 'design_id' => '7',
            'apply_to_parent' => '1', 'hide_lower_priority' => '0',
            'store_ids' => ['1', '2'], 'customer_group_ids' => ['0', '1'],
            'valid_from' => '2026-11-27 00:00:00', 'valid_to' => '2026-11-30',
        ]);

        self::assertSame('Black Friday', $label->getName());
        self::assertSame(1, $label->getStatus());
        self::assertSame(3, $label->getPriority());
        self::assertSame(7, $label->getDesignId());
        self::assertTrue($label->isApplyToParent());
        self::assertFalse($label->isHideLowerPriority());
        self::assertSame([1, 2], $label->getStoreIds());
        self::assertSame([0, 1], $label->getCustomerGroupIds(), 'group 0 is NOT LOGGED IN, not "all"');
        self::assertSame('2026-11-26 23:00:00', $label->getValidFrom());
        self::assertSame('2026-11-30 22:59:59', $label->getValidTo());
    }

    public function testAllStoreViewsAndEmptyDatesMeanNoRestriction(): void
    {
        $label = $this->label();

        $this->mapper()->apply($label, ['name' => 'x', 'store_ids' => ['0'], 'valid_from' => '', 'valid_to' => '']);

        self::assertSame([], $label->getStoreIds());
        self::assertSame([], $label->getCustomerGroupIds());
        self::assertNull($label->getValidFrom());
        self::assertNull($label->getValidTo());
    }

    public function testPlacementRowsBecomePlacementsAndDeletedRowsAreSkipped(): void
    {
        $label = $this->label();

        $this->mapper()->apply($label, ['name' => 'x', 'placements' => [
            ['area' => 'listing', 'position' => 'tr', 'design_id' => '', 'offset_x' => '4', 'max_labels' => '2', 'stacking' => 'horizontal', 'gap' => '6'],
            ['area' => 'product', 'position' => 'tl', 'delete' => 'true'],
            ['area' => 'product', 'position' => 'bl', 'design_id' => '9', 'pin_physical_side' => '1'],
        ]]);

        $placements = $label->getPlacements();
        self::assertCount(2, $placements);
        self::assertSame(['listing', 'tr', null, 4, 0, 2, 'horizontal', 6], [
            $placements[0]->getArea(), $placements[0]->getPosition(), $placements[0]->getDesignId(),
            $placements[0]->getOffsetX(), $placements[0]->getOffsetY(), $placements[0]->getMaxLabels(),
            $placements[0]->getStacking(), $placements[0]->getGap(),
        ]);
        self::assertSame(9, $placements[1]->getDesignId());
        self::assertTrue($placements[1]->isPinPhysicalSide());
        self::assertSame(3, $placements[1]->getMaxLabels(), 'default max labels');
        self::assertSame('vertical', $placements[1]->getStacking());
        self::assertSame(4, $placements[1]->getGap());
    }

    public function testMissingPlacementsKeyKeepsExistingPlacements(): void
    {
        $label = $this->label();
        $label->setPlacements([$this->objectManager->getObject(Placement::class)->setArea('listing')->setPosition('tl')]);

        $this->mapper()->apply($label, ['name' => 'inline']);

        self::assertCount(1, $label->getPlacements());
    }
}
