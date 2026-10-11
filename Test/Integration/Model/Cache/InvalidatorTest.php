<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Test\Integration\Model\Cache;

use Iranimij\OpenLabel\Api\DesignRepositoryInterface;
use Iranimij\OpenLabel\Model\Cache\Invalidator;
use Iranimij\OpenLabel\Test\Fixture\Design as DesignFixture;
use Magento\Framework\App\CacheInterface;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Theme block caches (Hyvä caches every product card for an hour) live in the application cache, so label and
 * design changes must clean it too, not only the page cache (found by the M3 storefront purge spec).
 */
#[DbIsolation(true)]
class InvalidatorTest extends TestCase
{
    private ?CacheInterface $cache = null;

    protected function setUp(): void
    {
        $this->cache = Bootstrap::getObjectManager()->get(CacheInterface::class);
    }

    public function testLabelTagsAreCleanedFromTheApplicationCache(): void
    {
        $this->cache->save('card', 'ol_test_card_label', ['openlabel_77']);
        $this->cache->save('other', 'ol_test_card_other', ['openlabel_78']);

        Bootstrap::getObjectManager()->get(Invalidator::class)->clean([], [77]);

        self::assertFalse($this->cache->load('ol_test_card_label'));
        self::assertSame('other', $this->cache->load('ol_test_card_other'));
    }

    public function testProductTagsAreCleanedFromTheApplicationCache(): void
    {
        $this->cache->save('card', 'ol_test_card_product', ['cat_p_5']);

        Bootstrap::getObjectManager()->get(Invalidator::class)->clean([5]);

        self::assertFalse($this->cache->load('ol_test_card_product'));
    }

    #[DataFixture(DesignFixture::class, [], 'design')]
    public function testDesignSaveCleansTheCardsShowingIt(): void
    {
        $id = (int) DataFixtureStorageManager::getStorage()->get('design')->getDesignId();
        $this->cache->save('card', 'ol_test_card_design', ['openlabel_design_' . $id]);
        $repository = Bootstrap::getObjectManager()->get(DesignRepositoryInterface::class);
        $design = $repository->getById($id);

        $repository->save($design->setText('Changed text only'));

        self::assertFalse($this->cache->load('ol_test_card_design'), 'a store-text-only change cleans too');
    }
}
