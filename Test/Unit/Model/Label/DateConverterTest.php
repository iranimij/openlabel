<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Test\Unit\Model\Label;

use Iranimij\OpenLabel\Model\Label\DateConverter;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class DateConverterTest extends TestCase
{
    private function converter(string $timezone = 'Europe/Berlin', string $locale = 'en_US'): DateConverter
    {
        $tz = $this->createMock(TimezoneInterface::class);
        $tz->method('getConfigTimezone')->willReturn($timezone);
        $resolver = $this->createMock(ResolverInterface::class);
        $resolver->method('getLocale')->willReturn($locale);

        return new DateConverter($tz, $resolver);
    }

    /**
     * @dataProvider toUtcCases
     */
    #[DataProvider('toUtcCases')]
    public function testToUtc(string $input, bool $endOfDay, string $expected): void
    {
        self::assertSame($expected, $this->converter()->toUtc($input, $endOfDay));
    }

    /**
     * @return array<string, array{string, bool, string}>
     */
    public static function toUtcCases(): array
    {
        return [
            'winter time is UTC+1' => ['2026-01-15 10:00:00', false, '2026-01-15 09:00:00'],
            'summer time is UTC+2' => ['2026-07-15 10:00', false, '2026-07-15 08:00:00'],
            'date only starts at local midnight' => ['2026-11-01', false, '2026-10-31 23:00:00'],
            'date only as end date runs to the end of the local day' => ['2026-11-30', true, '2026-11-30 22:59:59'],
            'day after the spring DST switch' => ['2026-03-29 12:00', false, '2026-03-29 10:00:00'],
            'night of the autumn DST switch' => ['2026-10-25 01:30', false, '2026-10-24 23:30:00'],
            'admin date picker format (en_US)' => ['11/30/2026', false, '2026-11-29 23:00:00'],
            'ISO with T separator' => ['2026-07-15T10:00', false, '2026-07-15 08:00:00'],
        ];
    }

    public function testEmptyValueIsNull(): void
    {
        self::assertNull($this->converter()->toUtc(''));
        self::assertNull($this->converter()->toUtc(null));
        self::assertNull($this->converter()->toLocal(null));
    }

    public function testGermanLocaleDate(): void
    {
        self::assertSame('2026-11-29 23:00:00', $this->converter('Europe/Berlin', 'de_DE')->toUtc('30.11.2026'));
    }

    public function testToLocalIsTheInverse(): void
    {
        $converter = $this->converter();

        self::assertSame('2026-07-15 10:00:00', $converter->toLocal('2026-07-15 08:00:00'));
        self::assertSame('2026-07-15 10:00:00', $converter->toLocal($converter->toUtc('2026-07-15 10:00:00')));
    }

    public function testGarbageIsRejectedWithAnInstruction(): void
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('Enter the date as YYYY-MM-DD');

        $this->converter()->toUtc('next friday-ish');
    }
}
