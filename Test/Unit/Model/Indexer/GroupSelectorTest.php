<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Test\Unit\Model\Indexer;

use Iranimij\OpenLabel\Api\Data\LabelInterface;
use Iranimij\OpenLabel\Model\Indexer\GroupSelector;
use Magento\Customer\Model\ResourceModel\Group\Collection;
use Magento\Customer\Model\ResourceModel\Group\CollectionFactory;
use PHPUnit\Framework\TestCase;

class GroupSelectorTest extends TestCase
{
    public function testLabelsWithoutPriceConditionsWriteOneRowForAllGroups(): void
    {
        self::assertSame([GroupSelector::ALL_GROUPS], $this->selector()->forLabel($this->label([1, 2]), false));
    }

    public function testPriceConditionsUseTheLabelsGroups(): void
    {
        self::assertSame([1, 2], $this->selector()->forLabel($this->label([1, 2]), true));
    }

    public function testPriceConditionsWithoutLabelGroupsUseEveryGroup(): void
    {
        self::assertSame([0, 1, 2, 3], $this->selector()->forLabel($this->label([]), true));
    }

    /**
     * @param int[] $groups
     */
    private function label(array $groups): LabelInterface
    {
        $label = $this->createStub(LabelInterface::class);
        $label->method('getCustomerGroupIds')->willReturn($groups);

        return $label;
    }

    private function selector(): GroupSelector
    {
        $collection = $this->createStub(Collection::class);
        $collection->method('getAllIds')->willReturn(['0', '1', '2', '3']);
        $factory = $this->createStub(CollectionFactory::class);
        $factory->method('create')->willReturn($collection);

        return new GroupSelector($factory);
    }
}
