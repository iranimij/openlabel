<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Test\Unit\Model\Css;

use Iranimij\OpenLabel\Model\Css\Sanitizer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class SanitizerTest extends TestCase
{
    public function testHarmlessCssIsKept(): void
    {
        $css = ".ol-d-3 { letter-spacing: .05em; text-transform: uppercase; }\n.ol-d-3:hover{opacity:.9}";

        self::assertSame($css, (new Sanitizer())->sanitize($css));
    }

    /**
     * @dataProvider dangerous
     */
    #[DataProvider('dangerous')]
    public function testDangerousConstructsAreRemoved(string $css, string $forbidden): void
    {
        $clean = (new Sanitizer())->sanitize($css);

        self::assertStringNotContainsStringIgnoringCase($forbidden, $clean);
        self::assertStringContainsString('color:red', str_replace(' ', '', $clean), 'neighbouring rules survive');
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function dangerous(): array
    {
        return [
            'expression()' => ['.a{width:expression(alert(1));color:red}', 'expression'],
            'expression hidden by a comment' => ['.a{width:expr/**/ession(alert(1));color:red}', 'ession('],
            'javascript url' => ['.a{background:url( "javascript:alert(1)" );color:red}', 'javascript'],
            'vbscript url' => ['.a{background:url(vbscript:x);color:red}', 'vbscript'],
            'data url' => ['.a{background:url(data:text/html;base64,AAAA);color:red}', 'data:'],
            '@import' => ['@import url("https://evil.test/x.css");.a{color:red}', '@import'],
            'behavior' => ['.a{behavior:url(x.htc);color:red}', 'behavior'],
            '-moz-binding' => ['.a{-moz-binding:url(x.xml#y);color:red}', 'binding'],
            'closing style tag' => ['.a{color:red}</style><script>alert(1)</script>', '</style'],
            'css escapes' => ['.a{background:u\\72l(javascript:x);color:red}', '\\'],
        ];
    }

    public function testEmptyInputGivesNull(): void
    {
        self::assertNull((new Sanitizer())->sanitize("  \n "));
        self::assertNull((new Sanitizer())->sanitize(null));
    }
}
