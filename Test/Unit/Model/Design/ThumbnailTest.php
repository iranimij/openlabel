<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Test\Unit\Model\Design;

use Iranimij\OpenLabel\Model\Design\Thumbnail;
use Magento\Framework\Escaper;
use PHPUnit\Framework\TestCase;

class ThumbnailTest extends TestCase
{
    private function thumbnail(): Thumbnail
    {
        return new Thumbnail(new Escaper());
    }

    public function testTextDesignUsesPresentationAttributesNotStyles(): void
    {
        $svg = $this->thumbnail()->render([
            'type' => 'shape', 'shape' => 'pill', 'bg_color' => '#b91c1c', 'text_color' => '#ffffff', 'text' => 'Sale',
        ]);

        self::assertStringStartsWith('<svg', $svg);
        self::assertStringContainsString('fill="#b91c1c"', $svg);
        self::assertStringContainsString('fill="#ffffff"', $svg);
        self::assertStringContainsString('>Sale</text>', $svg);
        self::assertStringNotContainsString('style=', $svg);
    }

    public function testTextIsEscapedAndShortened(): void
    {
        $svg = $this->thumbnail()->render(['type' => 'text', 'text' => '<script>x</script> a very long label text']);

        self::assertStringNotContainsString('<script>', $svg);
        self::assertStringContainsString('…', $svg);
    }

    public function testInvalidColoursFallBackToNeutral(): void
    {
        $svg = $this->thumbnail()->render(['type' => 'text', 'bg_color' => 'red" onload="x', 'text' => 'A']);

        self::assertStringNotContainsString('onload', $svg);
        self::assertStringContainsString('fill="#6b7280"', $svg);
    }

    public function testCircleShapeDrawsACircle(): void
    {
        self::assertStringContainsString('<circle', $this->thumbnail()->render(['type' => 'shape', 'shape' => 'circle', 'text' => '%']));
    }

    public function testImageDesignShowsTheImage(): void
    {
        $html = $this->thumbnail()->render(['type' => 'image', 'image_url' => 'https://example.test/media/a.svg', 'alt_text' => 'Sale']);

        self::assertStringContainsString('<img', $html);
        self::assertStringContainsString('alt="Sale"', $html);
    }
}
