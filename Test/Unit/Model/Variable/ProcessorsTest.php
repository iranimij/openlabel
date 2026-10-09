<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Test\Unit\Model\Variable;

use Iranimij\OpenLabel\Api\VariableProcessorInterface;
use Iranimij\OpenLabel\Model\Condition\Stock\StockDataInterface;
use Iranimij\OpenLabel\Model\Condition\Stock\StockDataResolver;
use Iranimij\OpenLabel\Model\Variable\Context;
use Iranimij\OpenLabel\Model\Variable\Processor\Attr;
use Iranimij\OpenLabel\Model\Variable\Processor\Br;
use Iranimij\OpenLabel\Model\Variable\Processor\FinalPrice;
use Iranimij\OpenLabel\Model\Variable\Processor\NewFor;
use Iranimij\OpenLabel\Model\Variable\Processor\Price;
use Iranimij\OpenLabel\Model\Variable\Processor\Rating;
use Iranimij\OpenLabel\Model\Variable\Processor\ReviewCount;
use Iranimij\OpenLabel\Model\Variable\Processor\ReviewSummary;
use Iranimij\OpenLabel\Model\Variable\ProductPrices;
use Magento\Framework\App\ResourceConnection;
use Iranimij\OpenLabel\Model\Variable\Processor\SaveAmount;
use Iranimij\OpenLabel\Model\Variable\Processor\SavePercent;
use Iranimij\OpenLabel\Model\Variable\Processor\Sku;
use Iranimij\OpenLabel\Model\Variable\Processor\SpecialEndDate;
use Iranimij\OpenLabel\Model\Variable\Processor\SpecialEndsIn;
use Iranimij\OpenLabel\Model\Variable\Processor\SpecialPrice;
use Iranimij\OpenLabel\Model\Variable\Processor\StockQty;
use Iranimij\OpenLabel\Model\Variable\Value;
use Magento\Catalog\Model\Product;
use Magento\Framework\DataObject;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * Every built-in variable on stub products. $now is 2026-10-09 12:00:00 UTC.
 */
class ProcessorsTest extends TestCase
{
    private const NOW = '2026-10-09 12:00:00';

    private ObjectManager $objectManager;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
    }

    public function testSavePercentIsRoundedAndZeroWithoutDiscount(): void
    {
        $value = $this->value(SavePercent::class, ['price' => 100.0, 'final_price' => 79.0]);
        self::assertSame(Value::NUMBER, $value->type);
        self::assertSame(21, $value->raw);
        self::assertSame(0, $this->value(SavePercent::class, ['price' => 100.0, 'final_price' => 100.0])->raw);
        self::assertTrue($this->value(SavePercent::class, ['price' => 100.0, 'final_price' => 100.0])->isEmpty());
        self::assertTrue($this->value(SavePercent::class, ['price' => 0.0, 'final_price' => 0.0])->isEmpty(), 'no regular price, no percent');
    }

    public function testSaveAmountIsACurrencyAmount(): void
    {
        $value = $this->value(SaveAmount::class, ['price' => 100.0, 'final_price' => 79.0]);
        self::assertSame(Value::CURRENCY, $value->type);
        self::assertSame(21.0, $value->raw);
        self::assertTrue($this->value(SaveAmount::class, ['price' => 50.0, 'final_price' => 50.0])->isEmpty());
    }

    public function testPricesAreCurrencyAmounts(): void
    {
        $data = ['price' => 100.0, 'final_price' => 79.0, 'special_price' => 79.0];
        self::assertSame(100.0, $this->value(Price::class, $data)->raw);
        self::assertSame(79.0, $this->value(FinalPrice::class, $data)->raw);
        self::assertSame(79.0, $this->value(SpecialPrice::class, $data)->raw);
        self::assertSame(Value::CURRENCY, $this->value(FinalPrice::class, $data)->type);
        self::assertTrue($this->value(SpecialPrice::class, ['price' => 100.0, 'final_price' => 100.0, 'special_price' => null])->isEmpty());
    }

    public function testStockQtyIsTheSalableQuantity(): void
    {
        $stock = $this->createStub(StockDataInterface::class);
        $stock->method('getSalableQty')->willReturn(3.0);
        $resolver = $this->createStub(StockDataResolver::class);
        $resolver->method('get')->willReturn($stock);

        $value = $this->value(StockQty::class, ['sku' => 'A'], ['stockDataResolver' => $resolver]);
        self::assertSame(Value::NUMBER, $value->type);
        self::assertSame(3.0, $value->raw);
        self::assertTrue($this->value(StockQty::class, ['sku' => 'C', 'type_id' => 'configurable'], ['stockDataResolver' => $resolver])->isEmpty());
    }

    public function testNewForCountsDaysSinceCreated(): void
    {
        $value = $this->value(NewFor::class, ['created_at' => '2026-09-29 08:00:00'], ['dateTime' => $this->dateTime()]);
        self::assertSame(10, $value->raw);
        self::assertTrue($this->value(NewFor::class, ['created_at' => null], ['dateTime' => $this->dateTime()])->isEmpty());
    }

    public function testSkuAndAttributeAreText(): void
    {
        self::assertSame('24-MB01', $this->value(Sku::class, ['sku' => '24-MB01'])->raw);
        self::assertSame(Value::TEXT, $this->value(Sku::class, ['sku' => '24-MB01'])->type);
        self::assertSame('Red', $this->value(Attr::class, ['color' => 'Red'], [], 'color')->raw);
        self::assertSame('Red, Blue', $this->value(Attr::class, ['color' => ['Red', 'Blue']], [], 'color')->raw);
        self::assertTrue($this->value(Attr::class, ['color' => 'Red'], [], 'size')->isEmpty());
        self::assertTrue($this->value(Attr::class, ['color' => 'Red'], [], '')->isEmpty());
    }

    public function testBrIsRawHtml(): void
    {
        $value = $this->value(Br::class, []);
        self::assertSame(Value::HTML, $value->type);
        self::assertSame('<br>', $value->raw);
        self::assertFalse($value->isEmpty());
    }

    public function testSpecialEndsInCountsDaysThenHours(): void
    {
        $dateTime = $this->dateTime();
        self::assertSame('3 days', (string) $this->value(SpecialEndsIn::class, ['special_to_date' => '2026-10-12 00:00:00', 'special_price' => 5.0], ['dateTime' => $dateTime])->raw);
        self::assertSame('1 day', (string) $this->value(SpecialEndsIn::class, ['special_to_date' => '2026-10-10 00:00:00', 'special_price' => 5.0], ['dateTime' => $dateTime])->raw);
        self::assertSame('12 hours', (string) $this->value(SpecialEndsIn::class, ['special_to_date' => '2026-10-09 00:00:00', 'special_price' => 5.0], ['dateTime' => $dateTime])->raw, 'valid through the day: ends at the next midnight');
        self::assertTrue($this->value(SpecialEndsIn::class, ['special_to_date' => '2026-10-01 00:00:00', 'special_price' => 5.0], ['dateTime' => $dateTime])->isEmpty());
        self::assertTrue($this->value(SpecialEndsIn::class, ['special_to_date' => null, 'special_price' => 5.0], ['dateTime' => $dateTime])->isEmpty());
    }

    public function testSpecialEndDateIsADate(): void
    {
        $value = $this->value(SpecialEndDate::class, ['special_to_date' => '2026-10-12 00:00:00'], ['dateTime' => $this->dateTime()]);
        self::assertSame(Value::DATE, $value->type);
        self::assertSame('2026-10-12 00:00:00', $value->raw);
        self::assertTrue($this->value(SpecialEndDate::class, ['special_to_date' => '2026-10-01 00:00:00'], ['dateTime' => $this->dateTime()])->isEmpty(), 'a past end date is not shown');
    }

    public function testRatingAndReviewCountReadTheSummaryInBothShapes(): void
    {
        self::assertSame(4.3, $this->value(Rating::class, ['rating_summary' => 85])->raw);
        self::assertSame(1, $this->value(Rating::class, ['rating_summary' => 85])->precision);
        self::assertSame(5, $this->value(ReviewCount::class, ['reviews_count' => 5])->raw);
        $summary = new DataObject(['rating_summary' => 90, 'reviews_count' => 7]);
        self::assertSame(4.5, $this->value(Rating::class, ['rating_summary' => $summary])->raw);
        self::assertSame(7, $this->value(ReviewCount::class, ['rating_summary' => $summary])->raw);
        self::assertTrue($this->value(Rating::class, [])->isEmpty());
        self::assertTrue($this->value(ReviewCount::class, [])->isEmpty());
    }

    /**
     * @param class-string<VariableProcessorInterface> $class
     * @param array<string, mixed> $productData
     * @param array<string, mixed> $arguments
     */
    private function value(string $class, array $productData, array $arguments = [], string $argument = ''): Value
    {
        /** @var VariableProcessorInterface $processor */
        $processor = $this->objectManager->getObject($class, $arguments + [
            'prices' => new ProductPrices(),
            'reviewSummary' => new ReviewSummary($this->createStub(ResourceConnection::class)),
        ]);
        /** @var Product $product */
        $product = $this->objectManager->getObject(Product::class);
        $product->setData($productData);

        return $processor->getValue($product, new Context(1, 1, 0), $argument);
    }

    private function dateTime(): DateTime
    {
        $dateTime = $this->createStub(DateTime::class);
        $dateTime->method('gmtDate')->willReturn(self::NOW);
        $dateTime->method('gmtTimestamp')->willReturn(strtotime(self::NOW));
        $dateTime->method('timestamp')->willReturnCallback(static fn ($v) => is_numeric($v) ? (int) $v : strtotime((string) $v));

        return $dateTime;
    }
}
