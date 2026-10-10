<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Model\Cache;

use Iranimij\OpenLabel\Model\Label;
use Magento\Catalog\Model\Product;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Indexer\CacheContext;

/**
 * Cleans full page cache (and Varnish through the core observer) for products whose labels changed,
 * bounded by the diff size, plus the per-label tags (02 · Architecture §5).
 */
class Invalidator
{
    /**
     * @param CacheContext $cacheContext
     * @param EventManager $eventManager
     */
    public function __construct(
        private readonly CacheContext $cacheContext,
        private readonly EventManager $eventManager
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
    }
}
