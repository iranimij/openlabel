<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Test\Unit\Model\Image;

use Iranimij\OpenLabel\Model\Image\SvgSanitizer;
use Magento\Framework\Exception\LocalizedException;
use PHPUnit\Framework\TestCase;

class SvgSanitizerTest extends TestCase
{
    private const BADGE = '<svg xmlns="http://www.w3.org/2000/svg" width="80" height="24" viewBox="0 0 80 24">'
        . '<rect width="80" height="24" rx="12" fill="#b91c1c"/><text x="40" y="16" fill="#fff">Sale</text></svg>';

    public function testCleanBadgeKeepsItsShapes(): void
    {
        $clean = (new SvgSanitizer())->sanitize(self::BADGE);

        self::assertStringContainsString('<rect', $clean);
        self::assertStringContainsString('>Sale</text>', $clean);
        self::assertStringContainsString('viewBox="0 0 80 24"', $clean);
    }

    public function testScriptsEventHandlersAndForeignObjectsAreRemoved(): void
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" onload="alert(1)"><script>alert(2)</script>'
            . '<rect width="1" height="1" onclick="alert(3)"/><foreignObject><div>x</div></foreignObject></svg>';

        $clean = (new SvgSanitizer())->sanitize($svg);

        self::assertStringNotContainsString('script', $clean);
        self::assertStringNotContainsString('alert', $clean);
        self::assertStringNotContainsString('foreignObject', $clean);
        self::assertStringContainsString('<rect', $clean);
    }

    public function testExternalReferencesAreRemovedButInternalOnesKept(): void
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">'
            . '<defs><path id="p" d="M0 0h1"/></defs><use xlink:href="#p"/><use href="https://evil.test/x.svg#a"/>'
            . '<image href="https://evil.test/track.png"/><a href="javascript:alert(1)"><rect width="1" height="1"/></a>'
            . '<rect width="1" height="1" style="fill:url(https://evil.test/x)"/></svg>';

        $clean = (new SvgSanitizer())->sanitize($svg);

        self::assertStringContainsString('#p', $clean);
        self::assertStringNotContainsString('evil.test', $clean);
        self::assertStringNotContainsString('javascript', $clean);
        self::assertStringNotContainsString('<image', $clean);
    }

    public function testDoctypeAndEntitiesAreRejected(): void
    {
        $this->expectException(LocalizedException::class);

        (new SvgSanitizer())->sanitize(
            '<?xml version="1.0"?><!DOCTYPE svg [<!ENTITY x SYSTEM "file:///etc/passwd">]>'
            . '<svg xmlns="http://www.w3.org/2000/svg"><text>&x;</text></svg>'
        );
    }

    public function testNonSvgIsRejected(): void
    {
        $this->expectException(LocalizedException::class);

        (new SvgSanitizer())->sanitize('<html><body>hi</body></html>');
    }

    public function testDimensionsComeFromAttributesOrViewBox(): void
    {
        $sanitizer = new SvgSanitizer();

        self::assertSame([80, 24], $sanitizer->dimensions(self::BADGE));
        self::assertSame([120, 40], $sanitizer->dimensions('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 120 40"/>'));
        self::assertSame([null, null], $sanitizer->dimensions('<svg xmlns="http://www.w3.org/2000/svg"/>'));
    }
}
