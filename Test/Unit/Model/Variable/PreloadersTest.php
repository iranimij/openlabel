<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Test\Unit\Model\Variable;

use Iranimij\OpenLabel\Model\Variable\Context;
use Iranimij\OpenLabel\Model\Variable\Processor\ReviewSummary;
use Iranimij\OpenLabel\Model\Variable\Processor\SoldLast30d;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * {SOLD_LAST_30D} and the review summary load their data for a whole listing with one query.
 */
class PreloadersTest extends TestCase
{
    public function testSoldLast30dLoadsAllProductsOnceAndDefaultsToZero(): void
    {
        $connection = $this->connection([['product_id' => 1, 'qty' => '3.0000']], expectedQueries: 1);
        $dateTime = $this->createStub(DateTime::class);
        $dateTime->method('gmtTimestamp')->willReturn(1_000_000);
        $dateTime->method('gmtDate')->willReturn('2026-09-09 12:00:00');
        $processor = new SoldLast30d($this->resource($connection), $dateTime);
        $context = new Context(1, 1, 0);

        $processor->preload([$this->product(1), $this->product(2)], $context);

        self::assertSame(3, $processor->getValue($this->product(1), $context)->raw);
        self::assertSame(0, $processor->getValue($this->product(2), $context)->raw);
        self::assertTrue($processor->getValue($this->product(0), $context)->isEmpty(), 'unsaved product');
    }

    public function testSoldLast30dLoadsLazilyWithoutPreload(): void
    {
        $connection = $this->connection([['product_id' => 5, 'qty' => '2']], expectedQueries: 1);
        $dateTime = $this->createStub(DateTime::class);
        $dateTime->method('gmtDate')->willReturn('2026-09-09 12:00:00');
        $processor = new SoldLast30d($this->resource($connection), $dateTime);

        self::assertSame(2, $processor->getValue($this->product(5), new Context(1, 1, 0))->raw);
    }

    public function testReviewSummaryPreloadsMissingProductsAndReadsEveryShape(): void
    {
        $connection = $this->connection([['entity_pk_value' => 2, 'rating_summary' => 80, 'reviews_count' => 4]], expectedQueries: 1);
        $summary = new ReviewSummary($this->resource($connection));
        $context = new Context(1, 1, 0);
        $loaded = $this->product(1);
        $loaded->setData('rating_summary', 60);
        $loaded->setData('reviews_count', 2);
        $missing = $this->product(2);
        $absent = $this->product(3);

        $summary->preload([$loaded, $missing, $absent], $context);

        self::assertSame(60, $summary->read($loaded, $context, 'rating_summary'));
        self::assertSame(2, $summary->read($loaded, $context, 'reviews_count'));
        self::assertSame(80, $summary->read($missing, $context, 'rating_summary'));
        self::assertSame(4, $summary->read($missing, $context, 'reviews_count'));
        self::assertNull($summary->read($absent, $context, 'rating_summary'));
        self::assertNull($summary->read($this->createStub(ProductInterface::class), $context, 'reviews_count'));
    }

    public function testReviewSummaryPreloadSkipsWhenNothingIsMissing(): void
    {
        $resource = $this->createMock(ResourceConnection::class);
        $resource->expects(self::never())->method('getConnection');
        $summary = new ReviewSummary($resource);
        $loaded = $this->product(1);
        $loaded->setData('rating_summary', 60);

        $summary->preload([$loaded], new Context(1, 1, 0));
        self::assertSame(60, $summary->read($loaded, new Context(1, 1, 0), 'rating_summary'));
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     */
    private function connection(array $rows, int $expectedQueries): AdapterInterface
    {
        $select = $this->createMock(Select::class);
        foreach (['from', 'where', 'group', 'order', 'limit', 'distinct', 'join', 'joinLeft'] as $method) {
            $select->method($method)->willReturnSelf();
        }
        $connection = $this->createMock(AdapterInterface::class);
        $connection->method('select')->willReturn($select);
        $connection->expects(self::exactly($expectedQueries))->method('fetchAll')->willReturn($rows);

        return $connection;
    }

    private function resource(AdapterInterface $connection): ResourceConnection
    {
        $resource = $this->createStub(ResourceConnection::class);
        $resource->method('getConnection')->willReturn($connection);
        $resource->method('getTableName')->willReturnArgument(0);

        return $resource;
    }

    private function product(int $id): Product
    {
        /** @var Product $product */
        $product = (new ObjectManager($this))->getObject(Product::class);
        $product->setData($id > 0 ? ['entity_id' => $id] : []);

        return $product;
    }
}
