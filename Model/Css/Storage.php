<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Model\Css;

use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Generated stylesheets live at `pub/media/openlabel/<store_id>/openlabel.<hash>.css`. The name changes with the
 * content, so the file can be cached forever. The current name is kept in the cache (no query on the storefront)
 * and recovered from the directory after a cache flush.
 */
class Storage
{
    public const DIRECTORY = 'openlabel';
    public const CACHE_KEY_PREFIX = 'openlabel_css_store_';

    /**
     * Previous files stay this long so pages still cached by Varnish or the FPC keep their stylesheet (one day of
     * page cache plus a margin).
     */
    public const KEEP_SECONDS = 2 * 86400;

    private const FILE_PATTERN = '/^openlabel\.[0-9a-f]{12}\.css$/';

    /**
     * @param Filesystem $filesystem
     * @param CacheInterface $cache
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        private readonly Filesystem $filesystem,
        private readonly CacheInterface $cache,
        private readonly StoreManagerInterface $storeManager
    ) {
    }

    /**
     * @param int $storeId
     * @param string $css
     * @return string path relative to the media directory
     */
    public function write(int $storeId, string $css): string
    {
        $path = $this->directory($storeId) . '/openlabel.' . substr(hash('sha256', $css), 0, 12) . '.css';
        $media = $this->media();
        if (!$media->isExist($path) || $media->readFile($path) !== $css) {
            $media->writeFile($path, $css);
        }
        $media->touch($path);
        $this->cache->save($path, self::CACHE_KEY_PREFIX . $storeId);
        $this->prune($storeId, $path);

        return $path;
    }

    /**
     * @param int $storeId
     * @return string|null path relative to the media directory, null before the first generation
     */
    public function current(int $storeId): ?string
    {
        $cached = $this->cache->load(self::CACHE_KEY_PREFIX . $storeId);
        if (is_string($cached) && $cached !== '' && $this->media()->isExist($cached)) {
            return $cached;
        }
        $newest = null;
        $newestTime = -1;
        foreach ($this->files($storeId) as $path) {
            $time = (int) ($this->media()->stat($path)['mtime'] ?? 0);
            if ($time > $newestTime) {
                $newest = $path;
                $newestTime = $time;
            }
        }
        if ($newest !== null) {
            $this->cache->save($newest, self::CACHE_KEY_PREFIX . $storeId);
        }

        return $newest;
    }

    /**
     * @param int $storeId
     * @param string $path
     * @return string
     */
    public function url(int $storeId, string $path): string
    {
        $store = $this->storeManager->getStore($storeId);
        $base = $store instanceof Store ? $store->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) : '/media/';

        return rtrim((string) $base, '/') . '/' . $path;
    }

    /**
     * @param int $storeId
     * @param string $keep
     * @return void
     */
    private function prune(int $storeId, string $keep): void
    {
        $limit = time() - self::KEEP_SECONDS;
        foreach ($this->files($storeId) as $path) {
            if ($path !== $keep && (int) ($this->media()->stat($path)['mtime'] ?? 0) < $limit) {
                $this->media()->delete($path);
            }
        }
    }

    /**
     * @param int $storeId
     * @return string[]
     */
    private function files(int $storeId): array
    {
        $directory = $this->directory($storeId);
        if (!$this->media()->isDirectory($directory)) {
            return [];
        }
        $files = [];
        foreach ($this->media()->read($directory) as $path) {
            if (preg_match(self::FILE_PATTERN, substr($path, (int) strrpos('/' . $path, '/'))) === 1) {
                $files[] = $path;
            }
        }

        return $files;
    }

    /**
     * @param int $storeId
     * @return string
     */
    private function directory(int $storeId): string
    {
        return self::DIRECTORY . '/' . $storeId;
    }

    /**
     * @return WriteInterface
     */
    private function media(): WriteInterface
    {
        return $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
    }
}
