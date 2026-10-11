<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Model\Cache;

use Iranimij\OpenLabel\Model\Design;
use Iranimij\OpenLabel\Model\Label;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Indexer\CacheContext;

/**
 * Cleans full page cache (and Varnish through the core observer) for products whose labels changed,
 * bounded by the diff size, plus the per-label tags (02 · Architecture §5). The same tags are cleaned from the
 * application cache, where theme block caches keep product cards (Hyvä: one hour per card).
 */
class Invalidator
{
    /**
     * @param CacheContext $cacheContext
     * @param EventManager $eventManager
     * @param CacheInterface $appCache
     */
    public function __construct(
        private readonly CacheContext $cacheContext,
        private readonly EventManager $eventManager,
        private readonly CacheInterface $appCache
    ) {
    }

    /**
     * @param int[] $productIds
     * @param int[] $labelIds
     * @return void
     */
    public function clean(array $productIds, array $labelIds = []): void
    {
        $productIds = array_values(array_unique(array_map('intval', $productIds)));
        $tags = array_map(static fn (int $id): string => Label::CACHE_TAG_PREFIX . $id, array_map('intval', $labelIds));
        if ($productIds === [] && $tags === []) {
            return;
        }
        if ($productIds !== []) {
            $this->cacheContext->registerEntities(Product::CACHE_TAG, $productIds);
        }
        if ($tags !== []) {
            $this->cacheContext->registerTags($tags);
        }
        $this->eventManager->dispatch('clean_cache_by_tags', ['object' => $this->cacheContext]);
        $this->appCache->clean(array_merge(
            array_map(static fn (int $id): string => Product::CACHE_TAG . '_' . $id, $productIds),
            $tags
        ));
    }

    /**
     * Pages and cards showing a design (its colours or store texts changed).
     *
     * @param int $designId
     * @return void
     */
    public function cleanDesign(int $designId): void
    {
        $tag = Design::CACHE_TAG . '_' . $designId;
        $this->cacheContext->registerTags([$tag]);
        $this->eventManager->dispatch('clean_cache_by_tags', ['object' => $this->cacheContext]);
        $this->appCache->clean([$tag]);
    }
}
