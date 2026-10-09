<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Test\Unit\Model\Indexer;

use Iranimij\OpenLabel\Model\Indexer\Product;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Lock\LockManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

class ProductTest extends TestCase
{
    public function testFullReindexRefusesToRunWhileAnotherOneHoldsTheLock(): void
    {
        $lockManager = $this->createMock(LockManagerInterface::class);
        $lockManager->method('isLocked')->with(Product::LOCK_NAME)->willReturn(true);
        $lockManager->expects(self::never())->method('lock');
        /** @var Product $indexer */
        $indexer = (new ObjectManager($this))->getObject(Product::class, ['lockManager' => $lockManager]);

        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('already running');

        $indexer->executeFull();
    }

    public function testFullReindexRefusesWhenTheLockCannotBeTaken(): void
    {
        $lockManager = $this->createMock(LockManagerInterface::class);
        $lockManager->method('isLocked')->willReturn(false);
        $lockManager->method('lock')->with(Product::LOCK_NAME, 0)->willReturn(false);
        /** @var Product $indexer */
        $indexer = (new ObjectManager($this))->getObject(Product::class, ['lockManager' => $lockManager]);

        $this->expectException(LocalizedException::class);

        $indexer->executeFull();
    }
}
