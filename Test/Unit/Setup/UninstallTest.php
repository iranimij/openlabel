<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Test\Unit\Setup;

use Iranimij\OpenLabel\Setup\Uninstall;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use PHPUnit\Framework\TestCase;

class UninstallTest extends TestCase
{
    public function testDropsEveryOpenLabelTableAndConfig(): void
    {
        $connection = $this->createMock(AdapterInterface::class);
        $setup = $this->createMock(SchemaSetupInterface::class);
        $setup->method('getConnection')->willReturn($connection);
        $setup->method('getTable')->willReturnCallback(static fn (string $t): string => 'pfx_' . $t);

        $dropped = [];
        $connection->method('isTableExists')->willReturn(true);
        $connection->method('dropTable')->willReturnCallback(static function (string $table) use (&$dropped): bool {
            $dropped[] = $table;
            return true;
        });
        $connection->expects(self::once())->method('delete')
            ->with('pfx_core_config_data', ['path LIKE ?' => 'openlabel/%']);

        (new Uninstall())->uninstall($setup, $this->createMock(ModuleContextInterface::class));

        self::assertSame([
            'pfx_openlabel_index_replica',
            'pfx_openlabel_index',
            'pfx_openlabel_placement',
            'pfx_openlabel_design_store',
            'pfx_openlabel_label',
            'pfx_openlabel_design',
        ], $dropped);
    }
}
