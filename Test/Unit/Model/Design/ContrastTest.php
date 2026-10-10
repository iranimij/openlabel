<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Test\Unit\Model\Design;

use Iranimij\OpenLabel\Model\Design\Contrast;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ContrastTest extends TestCase
{
    private Contrast $contrast;

    protected function setUp(): void
    {
        $this->contrast = new Contrast();
    }

    public function testBlackOnWhiteIsTheMaximumRatio(): void
    {
        self::assertEqualsWithDelta(21.0, $this->contrast->ratio('#000000', '#ffffff'), 0.001);
    }

    public function testRatioIsSymmetric(): void
    {
        self::assertSame($this->contrast->ratio('#e11d48', '#ffffff'), $this->contrast->ratio('#ffffff', '#e11d48'));
    }

    public function testShortAndAlphaHexAreAccepted(): void
    {
        self::assertSame($this->contrast->ratio('#ffffff', '#000000'), $this->contrast->ratio('#fff', '#000000cc'));
    }

    public function testInvalidColourHasNoRatio(): void
    {
        self::assertNull($this->contrast->ratio('red', '#ffffff'));
        self::assertSame(Contrast::LEVEL_UNKNOWN, $this->contrast->level(null, '#ffffff'));
    }

    /**
     * @dataProvider levels
     */
    #[DataProvider('levels')]
    public function testLevel(string $foreground, string $background, string $expected): void
    {
        self::assertSame($expected, $this->contrast->level($foreground, $background));
    }

    /**
     * @return array<string, array{string, string, string}>
     */
    public static function levels(): array
    {
        return [
            'white on yellow fails' => ['#ffffff', '#facc15', Contrast::LEVEL_FAIL],
            'white on #767676 passes at the 4.5 boundary' => ['#ffffff', '#767676', Contrast::LEVEL_PASS],
            'white on #949494 is large-text only' => ['#ffffff', '#949494', Contrast::LEVEL_LARGE],
            'dark on amber passes' => ['#1f2937', '#fbbf24', Contrast::LEVEL_PASS],
        ];
    }
}
