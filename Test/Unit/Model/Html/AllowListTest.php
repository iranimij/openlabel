<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Test\Unit\Model\Html;

use Iranimij\OpenLabel\Model\Html\AllowList;
use Magento\Framework\Escaper;
use PHPUnit\Framework\TestCase;

class AllowListTest extends TestCase
{
    public function testKeepsInlineFormattingAndDropsEverythingElse(): void
    {
        $allowList = new AllowList(new Escaper());

        $html = $allowList->clean('<b>Sale</b> <em>-20%</em><br><sup>*</sup> <a href="x">no</a> <script>alert(1)</script><span onclick="evil()" class="c">s</span>');

        self::assertStringContainsString('<b>Sale</b> <em>-20%</em><br><sup>*</sup>', $html);
        self::assertStringNotContainsString('<a', $html);
        self::assertStringNotContainsString('<script', $html);
        self::assertStringNotContainsString('onclick', $html);
        self::assertStringContainsString('<span class="c">s</span>', $html);
        self::assertSame(['b', 'strong', 'i', 'em', 'br', 'span', 'small', 'sup', 'sub'], AllowList::TAGS);
    }

    public function testPlainTextWithEntitiesIsEscapedOnce(): void
    {
        self::assertSame('Tom &amp; Jerry', (new AllowList(new Escaper()))->clean('Tom & Jerry'));
    }
}
