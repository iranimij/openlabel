<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Test\Unit\Model\Condition;

use Iranimij\OpenLabel\Model\Condition\IsNew;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Framework\Stdlib\DateTime\DateTime;

class IsNewTest extends ConditionTestCase
{
    private const NOW = '2026-10-09 12:00:00';

    public function testNewsDatesMatchWhenNowIsInsideTheWindow(): void
    {
        $condition = $this->isNew('news_dates', '==', '1');

        self::assertTrue($condition->validate($this->product(['news_from_date' => '2026-10-01 00:00:00', 'news_to_date' => null])));
        self::assertTrue($condition->validate($this->product(['news_from_date' => '2026-10-01 00:00:00', 'news_to_date' => '2026-10-20 00:00:00'])));
        self::assertFalse($condition->validate($this->product(['news_from_date' => '2026-10-01 00:00:00', 'news_to_date' => '2026-10-05 00:00:00'])));
        self::assertFalse($condition->validate($this->product(['news_from_date' => null, 'news_to_date' => null])));
    }

    public function testDaysSinceCreatedUsesWholeDays(): void
    {
        $condition = $this->isNew('days_since_created', '<=', '30');

        self::assertTrue($condition->validate($this->product(['created_at' => '2026-09-20 08:00:00'])));
        self::assertFalse($condition->validate($this->product(['created_at' => '2026-08-20 08:00:00'])));
    }

    public function testDaysSinceUpdatedUsesUpdatedAt(): void
    {
        $condition = $this->isNew('days_since_updated', '<=', '7');

        self::assertTrue($condition->validate($this->product(['created_at' => '2026-01-01 00:00:00', 'updated_at' => '2026-10-05 00:00:00'])));
    }

    public function testSqlJoinsNewsDatesFromTheDefaultScopeAndNeedsNoGroup(): void
    {
        $condition = $this->isNew('news_dates', '==', '1');

        $joins = $condition->getTablesToJoin();
        self::assertSame(['ol_news_from', 'ol_news_to'], array_keys($joins));
        self::assertSame('ol_news_from.entity_id = e.entity_id AND ol_news_from.attribute_id = 42 AND ol_news_from.store_id = 0', $joins['ol_news_from']['condition']);
        self::assertFalse($condition->requiresCustomerGroup());
        self::assertTrue($condition->isDateRelative(), 'news_dates changes with time: the daily cron must reindex it');

        self::assertSame([], $this->isNew('days_since_created', '<=', '30')->getTablesToJoin());
        self::assertStringContainsString('DATEDIFF', (string) $this->isNew('days_since_created', '<=', '30')->getMappedSqlField());
    }

    private function isNew(string $attribute, string $operator, string $value): IsNew
    {
        $dateTime = $this->createStub(DateTime::class);
        $dateTime->method('gmtDate')->willReturn(self::NOW);
        $dateTime->method('gmtTimestamp')->willReturn(strtotime(self::NOW));
        $dateTime->method('timestamp')->willReturnCallback(static fn ($v) => is_numeric($v) ? (int) $v : strtotime((string) $v));
        $eavAttribute = $this->createStub(Attribute::class);
        $eavAttribute->method('getId')->willReturn('42');
        $eavConfig = $this->createStub(EavConfig::class);
        $eavConfig->method('getAttribute')->willReturn($eavAttribute);
        $productResource = $this->createStub(ProductResource::class);
        $productResource->method('getLinkField')->willReturn('entity_id');
        /** @var IsNew $condition */
        $condition = $this->condition(IsNew::class, $attribute, $operator, $value, [
            'dateTime' => $dateTime,
            'eavConfig' => $eavConfig,
            'productResource' => $productResource,
        ]);

        return $condition;
    }
}
