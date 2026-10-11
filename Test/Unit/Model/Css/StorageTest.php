<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Test\Unit\Model\Css;

use Iranimij\OpenLabel\Model\Css\Regenerator;
use Iranimij\OpenLabel\Model\Css\Storage;
use Iranimij\OpenLabel\Model\Css\StylesheetGenerator;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\TestCase;

class StorageTest extends TestCase
{
    /** @var array<string, array{content: string, mtime: int}> */
    private array $files = [];

    /** @var array<string, string> */
    private array $cache = [];

    public function testWriteNamesTheFileByContentHashAndRemembersIt(): void
    {
        $path = $this->storage()->write(2, '.a{}');

        self::assertSame('openlabel/2/openlabel.' . substr(hash('sha256', '.a{}'), 0, 12) . '.css', $path);
        self::assertSame('.a{}', $this->files[$path]['content']);
        self::assertSame($path, $this->cache[Storage::CACHE_KEY_PREFIX . '2']);
    }

    public function testWritePrunesOnlyFilesOlderThanTheCacheWindow(): void
    {
        $this->files['openlabel/2/openlabel.aaaaaaaaaaaa.css'] = ['content' => 'old', 'mtime' => time() - 3 * 86400];
        $this->files['openlabel/2/openlabel.bbbbbbbbbbbb.css'] = ['content' => 'recent', 'mtime' => time() - 3600];
        $this->files['openlabel/2/notes.txt'] = ['content' => 'foreign', 'mtime' => 0];

        $this->storage()->write(2, '.b{}');

        self::assertArrayNotHasKey('openlabel/2/openlabel.aaaaaaaaaaaa.css', $this->files);
        self::assertArrayHasKey('openlabel/2/openlabel.bbbbbbbbbbbb.css', $this->files);
        self::assertArrayHasKey('openlabel/2/notes.txt', $this->files, 'only our own files are pruned');
    }

    public function testCurrentPrefersTheCacheAndFallsBackToTheNewestFile(): void
    {
        $this->files['openlabel/1/openlabel.aaaaaaaaaaaa.css'] = ['content' => 'a', 'mtime' => 100];
        $this->files['openlabel/1/openlabel.bbbbbbbbbbbb.css'] = ['content' => 'b', 'mtime' => 200];
        $storage = $this->storage();

        self::assertSame('openlabel/1/openlabel.bbbbbbbbbbbb.css', $storage->current(1));
        $this->cache[Storage::CACHE_KEY_PREFIX . '1'] = 'openlabel/1/openlabel.aaaaaaaaaaaa.css';
        self::assertSame('openlabel/1/openlabel.aaaaaaaaaaaa.css', $storage->current(1));
        self::assertNull($storage->current(5));
    }

    public function testCachedNameOfADeletedFileIsIgnored(): void
    {
        $this->cache[Storage::CACHE_KEY_PREFIX . '1'] = 'openlabel/1/openlabel.cccccccccccc.css';

        self::assertNull($this->storage()->current(1));
    }

    public function testUrlUsesTheStoreMediaUrl(): void
    {
        self::assertSame(
            'https://shop.test/media/openlabel/1/openlabel.x.css',
            $this->storage()->url(1, 'openlabel/1/openlabel.x.css')
        );
    }

    public function testRegeneratorBuildsOnceAndWritesEveryStore(): void
    {
        $generator = $this->createMock(StylesheetGenerator::class);
        $generator->expects(self::once())->method('build')->willReturn('.c{}');
        $regenerator = new Regenerator($generator, $this->storage(), $this->storeManager());

        $paths = $regenerator->regenerateAll();

        self::assertSame([1, 2], array_keys($paths));
        self::assertSame('.c{}', $this->files[$paths[2]]['content']);
    }

    public function testEnsureOnlyBuildsWhenThereIsNoFileYet(): void
    {
        $generator = $this->createMock(StylesheetGenerator::class);
        $generator->expects(self::once())->method('build')->willReturn('.d{}');
        $regenerator = new Regenerator($generator, $this->storage(), $this->storeManager());

        $first = $regenerator->ensure(1);

        self::assertSame($first, $regenerator->ensure(1));
    }

    private function storage(): Storage
    {
        $media = $this->createStub(WriteInterface::class);
        $media->method('isExist')->willReturnCallback(fn (string $p): bool => isset($this->files[$p]));
        $media->method('readFile')->willReturnCallback(fn (string $p): string => $this->files[$p]['content']);
        $media->method('writeFile')->willReturnCallback(function (string $p, string $c): int {
            $this->files[$p] = ['content' => $c, 'mtime' => time()];

            return strlen($c);
        });
        $media->method('touch')->willReturnCallback(function (string $p): bool {
            $this->files[$p]['mtime'] = time();

            return true;
        });
        $media->method('stat')->willReturnCallback(fn (string $p): array => ['mtime' => $this->files[$p]['mtime']]);
        $media->method('delete')->willReturnCallback(function (string $p): bool {
            unset($this->files[$p]);

            return true;
        });
        $media->method('isDirectory')->willReturnCallback(fn (string $d): bool => $this->inDirectory($d) !== []);
        $media->method('read')->willReturnCallback(fn (string $d): array => $this->inDirectory($d));
        $filesystem = $this->createStub(Filesystem::class);
        $filesystem->method('getDirectoryWrite')->willReturn($media);

        $cache = $this->createStub(CacheInterface::class);
        $cache->method('load')->willReturnCallback(fn (string $k): string|false => $this->cache[$k] ?? false);
        $cache->method('save')->willReturnCallback(function (string $v, string $k): bool {
            $this->cache[$k] = $v;

            return true;
        });

        return new Storage($filesystem, $cache, $this->storeManager());
    }

    private function storeManager(): StoreManagerInterface
    {
        $stores = [];
        foreach ([2, 1] as $id) {
            $store = $this->createStub(Store::class);
            $store->method('getId')->willReturn($id);
            $store->method('getBaseUrl')->willReturn('https://shop.test/media/');
            $stores[$id] = $store;
        }
        $manager = $this->createStub(StoreManagerInterface::class);
        $manager->method('getStores')->willReturn($stores);
        $manager->method('getStore')->willReturnCallback(fn (int $id): StoreInterface => $stores[$id]);

        return $manager;
    }

    /**
     * @param string $directory
     * @return string[]
     */
    private function inDirectory(string $directory): array
    {
        return array_values(array_filter(
            array_keys($this->files),
            static fn (string $p): bool => str_starts_with($p, $directory . '/')
        ));
    }
}
