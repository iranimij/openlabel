<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Test\Unit;

use Magento\Framework\Component\ComponentRegistrar;
use PHPUnit\Framework\TestCase;

/**
 * Guards the module identity and the "only Magento core and iranimij/module-base" dependency rule.
 */
class ModuleTest extends TestCase
{
    private const MODULE_NAME = 'Iranimij_OpenLabel';

    public function testModuleIsRegisteredWithComponentRegistrar(): void
    {
        $path = (new ComponentRegistrar())->getPath(ComponentRegistrar::MODULE, self::MODULE_NAME);

        self::assertNotNull($path, 'registration.php must register ' . self::MODULE_NAME);
        self::assertSame(realpath(dirname(__DIR__, 2)), realpath((string) $path));
    }

    public function testModuleXmlDeclaresTheModuleAndLoadsAfterBase(): void
    {
        $xml = simplexml_load_file(dirname(__DIR__, 2) . '/etc/module.xml');

        self::assertNotFalse($xml);
        self::assertSame(self::MODULE_NAME, (string) $xml->module['name']);
        $sequence = array_map(static fn ($m) => (string) $m['name'], iterator_to_array($xml->module->sequence->module, false));
        self::assertContains('Iranimij_Base', $sequence);
        self::assertContains('Magento_Rule', $sequence);
    }

    public function testComposerRequiresOnlyCoreAndBase(): void
    {
        $composer = json_decode((string) file_get_contents(dirname(__DIR__, 2) . '/composer.json'), true);

        self::assertSame('magento2-module', $composer['type']);
        self::assertSame('MIT', $composer['license']);
        self::assertArrayHasKey('iranimij/module-base', $composer['require']);
        foreach (array_keys($composer['require']) as $package) {
            self::assertMatchesRegularExpression(
                '#^(php|ext-[a-z0-9_]+|magento/[a-z0-9-]+|iranimij/module-base)$#',
                $package,
                'Only php, extensions, magento/* and iranimij/module-base may be required'
            );
        }
    }
}
