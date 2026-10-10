<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Test\Unit\Model\Label;

use Iranimij\OpenLabel\Model\Label\ActiveWindow;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ActiveWindowTest extends TestCase
{
    /**
     * @dataProvider states
     */
    #[DataProvider('states')]
    public function testState(int $status, ?string $from, ?string $to, string $expected): void
    {
        $now = new \DateTimeImmutable('2026-10-10 12:00:00', new \DateTimeZone('UTC'));

        self::assertSame($expected, (new ActiveWindow())->state($status, $from, $to, $now));
    }

    /**
     * @return array<string, array{int, ?string, ?string, string}>
     */
    public static function states(): array
    {
        return [
            'no window' => [1, null, null, ActiveWindow::ACTIVE],
            'inside window' => [1, '2026-10-01 00:00:00', '2026-10-31 00:00:00', ActiveWindow::ACTIVE],
            'not started' => [1, '2026-11-01 00:00:00', null, ActiveWindow::SCHEDULED],
            'ended' => [1, null, '2026-10-09 23:59:59', ActiveWindow::EXPIRED],
            'disabled wins' => [0, null, null, ActiveWindow::DISABLED],
        ];
    }

    public function testDescribeWindow(): void
    {
        $window = new ActiveWindow();

        self::assertSame('Always', (string) $window->describe(null, null));
        self::assertSame('From 2026-11-01', (string) $window->describe('2026-11-01 00:00:00', null));
        self::assertSame('Until 2026-11-30', (string) $window->describe(null, '2026-11-30 23:59:59'));
        self::assertSame('2026-11-01 – 2026-11-30', (string) $window->describe('2026-11-01 00:00:00', '2026-11-30 23:59:59'));
    }
}
