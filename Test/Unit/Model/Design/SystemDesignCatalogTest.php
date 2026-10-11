<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Test\Unit\Model\Design;

use Iranimij\OpenLabel\Model\Design;
use Iranimij\OpenLabel\Model\Design\Contrast;
use Iranimij\OpenLabel\Model\Design\SystemDesignCatalog;
use Iranimij\OpenLabel\Model\Design\Validator;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

class SystemDesignCatalogTest extends TestCase
{
    public function testCatalogHasFifteenUniquelyNamedDesigns(): void
    {
        $definitions = (new SystemDesignCatalog())->getDefinitions();

        self::assertCount(15, $definitions);
        self::assertCount(15, array_unique(array_column($definitions, 'name')));
    }

    public function testStarterKeysPointAtCatalogEntries(): void
    {
        $catalog = new SystemDesignCatalog();

        foreach ([SystemDesignCatalog::SALE, SystemDesignCatalog::NEW, SystemDesignCatalog::LOW_STOCK] as $key) {
            self::assertArrayHasKey($key, $catalog->getDefinitions());
        }
    }

    public function testEveryDesignIsValidAndPassesContrast(): void
    {
        $objectManager = new ObjectManager($this);
        $validator = new Validator();
        $contrast = new Contrast();

        foreach ((new SystemDesignCatalog())->getDefinitions() as $key => $definition) {
            /** @var Design $design */
            $design = $objectManager->getObject(Design::class);
            $storeTexts = $definition['store_texts'];
            unset($definition['store_texts']);
            $design->setData($definition)->setStoreTexts($storeTexts);

            self::assertSame([], array_map('strval', $validator->validate($design)), $key . ' is valid');
            self::assertGreaterThanOrEqual(
                4.5,
                $contrast->ratio((string) $definition['text_color'], (string) $definition['bg_color']),
                $key . ' passes WCAG AA'
            );
            self::assertTrue($definition['is_system'], $key . ' is flagged as system');
        }
    }
}
