<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Test\Integration;

use Magento\Config\Model\Config\Structure\Data as ConfigStructureData;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @magentoAppArea adminhtml
 */
class ConfigSectionTest extends TestCase
{
    public function testOpenLabelSectionLivesUnderTheIranimijTab(): void
    {
        $data = Bootstrap::getObjectManager()->get(ConfigStructureData::class)->get();

        self::assertArrayHasKey('openlabel', $data['sections']);
        self::assertSame('iranimij', $data['sections']['openlabel']['tab']);
        self::assertSame('Iranimij_OpenLabel::config', $data['sections']['openlabel']['resource']);
        self::assertArrayHasKey('enabled', $data['sections']['openlabel']['children']['general']['children']);
    }

    public function testModuleIsEnabledByDefault(): void
    {
        $scopeConfig = Bootstrap::getObjectManager()->get(ScopeConfigInterface::class);

        self::assertTrue($scopeConfig->isSetFlag('openlabel/general/enabled'));
    }
}
