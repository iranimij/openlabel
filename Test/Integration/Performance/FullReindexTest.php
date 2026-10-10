<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Test\Integration\Performance;

use Iranimij\OpenLabel\Api\Data\LabelInterface;
use Iranimij\OpenLabel\Api\LabelRepositoryInterface;
use Iranimij\OpenLabel\Model\Condition\IsNew;
use Iranimij\OpenLabel\Model\Condition\OnSale;
use Iranimij\OpenLabel\Model\Condition\Stock;
use Iranimij\OpenLabel\Model\Indexer\Product as ProductIndexer;
use Iranimij\OpenLabel\Model\Rule\Condition\Combine;
use Iranimij\OpenLabel\Test\Fixture\Design as DesignFixture;
use Iranimij\OpenLabel\Test\Fixture\Label as LabelFixture;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

/**
 * Performance gate (02 · Architecture §10): a full reindex of a 2,000-product catalog with three labels in ≤ 10 s.
 * Runs on main and nightly only (OPENLABEL_PERF=true), because the catalog takes a while to build.
 *
 * @group performance
 */
#[Group('performance')]
#[DbIsolation(false)]
#[DataFixture(DesignFixture::class, [], 'design')]
class FullReindexTest extends TestCase
{
    private const PRODUCTS = 2000;
    private const BUDGET_SECONDS = 10.0;
    private const SKU_PREFIX = 'ol-perf-';

    /** @var LabelInterface[] */
    private array $labels = [];

    protected function setUp(): void
    {
        if (getenv('OPENLABEL_PERF') !== 'true') {
            self::markTestSkipped('Set OPENLABEL_PERF=true to run the 2,000-product reindex budget (main and nightly CI).');
        }
    }

    protected function tearDown(): void
    {
        $repository = Bootstrap::getObjectManager()->get(LabelRepositoryInterface::class);
        foreach ($this->labels as $label) {
            $repository->deleteById((int) $label->getLabelId());
        }
        $this->deleteProducts();
    }

    public function testFullReindexOfTwoThousandProductsStaysWithinTenSeconds(): void
    {
        $this->createProducts();
        $registry = Bootstrap::getObjectManager()->get(IndexerRegistry::class);
        foreach (['cataloginventory_stock', 'catalog_product_price'] as $id) {
            $registry->get($id)->reindexAll();
        }
        $this->labels[] = $this->label('Perf sale', [['type' => OnSale::class, 'attribute' => 'on_sale', 'operator' => '==', 'value' => '1']]);
        $this->labels[] = $this->label('Perf low stock', [['type' => Stock::class, 'attribute' => 'salable_qty', 'operator' => '<=', 'value' => '5']]);
        $this->labels[] = $this->label('Perf new', [['type' => IsNew::class, 'attribute' => 'days_since_created', 'operator' => '<=', 'value' => '365']]);
        $indexer = Bootstrap::getObjectManager()->get(ProductIndexer::class);

        $start = microtime(true);
        $indexer->executeFull();
        $seconds = microtime(true) - $start;

        $resource = Bootstrap::getObjectManager()->get(ResourceConnection::class);
        $rows = (int) $resource->getConnection()->fetchOne(
            $resource->getConnection()->select()->from($resource->getTableName('openlabel_index'), 'COUNT(*)')
                ->where('label_id IN (?)', array_map(static fn (LabelInterface $l) => $l->getLabelId(), $this->labels))
        );
        fwrite(STDERR, sprintf("\nOpenLabel full reindex: %d products in the catalog, %d index rows, %.2f s\n", $this->catalogSize(), $rows, $seconds));
        self::assertGreaterThan(self::PRODUCTS, $rows, 'every perf product is new and half of them are on sale');
        self::assertLessThanOrEqual(self::BUDGET_SECONDS, $seconds, sprintf('full reindex took %.2f s', $seconds));
    }

    /**
     * Inserts the products with plain SQL: the repository would take minutes for 2,000 products.
     */
    private function createProducts(): void
    {
        $this->deleteProducts();
        $om = Bootstrap::getObjectManager();
        $resource = $om->get(ResourceConnection::class);
        $connection = $resource->getConnection();
        $eavConfig = $om->get(\Magento\Eav\Model\Config::class);
        $attributeId = static fn (string $code): int => (int) $eavConfig->getAttribute(\Magento\Catalog\Model\Product::ENTITY, $code)->getId();
        $linkField = $om->get(\Magento\Framework\EntityManager\MetadataPool::class)
            ->getMetadata(\Magento\Catalog\Api\Data\ProductInterface::class)->getLinkField();
        $entityTable = $resource->getTableName('catalog_product_entity');
        $rows = [];
        for ($i = 1; $i <= self::PRODUCTS; $i++) {
            $rows[] = ['attribute_set_id' => 4, 'type_id' => 'simple', 'sku' => self::SKU_PREFIX . $i, 'has_options' => 0, 'required_options' => 0];
        }
        foreach (array_chunk($rows, 500) as $chunk) {
            $connection->insertMultiple($entityTable, $chunk);
        }
        $select = $connection->select()->from($entityTable, ['entity_id', $linkField, 'sku'])->where('sku LIKE ?', self::SKU_PREFIX . '%');
        $int = $varchar = $decimal = $website = $stockItem = $stockStatus = [];
        foreach ($connection->fetchAll($select) as $row) {
            $link = (int) $row[$linkField];
            $n = (int) substr($row['sku'], strlen(self::SKU_PREFIX));
            $int[] = [$linkField => $link, 'attribute_id' => $attributeId('status'), 'store_id' => 0, 'value' => 1];
            $int[] = [$linkField => $link, 'attribute_id' => $attributeId('visibility'), 'store_id' => 0, 'value' => 4];
            $varchar[] = [$linkField => $link, 'attribute_id' => $attributeId('name'), 'store_id' => 0, 'value' => 'Perf product ' . $n];
            $decimal[] = [$linkField => $link, 'attribute_id' => $attributeId('price'), 'store_id' => 0, 'value' => 100];
            if ($n % 2 === 0) {
                $decimal[] = [$linkField => $link, 'attribute_id' => $attributeId('special_price'), 'store_id' => 0, 'value' => 80];
            }
            $website[] = ['product_id' => (int) $row['entity_id'], 'website_id' => 1];
            $qty = $n % 10 === 0 ? 3 : 100;
            $stockItem[] = ['product_id' => (int) $row['entity_id'], 'stock_id' => 1, 'qty' => $qty, 'is_in_stock' => 1, 'website_id' => 0, 'manage_stock' => 1];
        }
        foreach ([
            'catalog_product_entity_int' => $int,
            'catalog_product_entity_varchar' => $varchar,
            'catalog_product_entity_decimal' => $decimal,
            'catalog_product_website' => $website,
            'cataloginventory_stock_item' => $stockItem,
        ] as $table => $data) {
            foreach (array_chunk($data, 1000) as $chunk) {
                $connection->insertMultiple($resource->getTableName($table), $chunk);
            }
        }
    }

    private function deleteProducts(): void
    {
        $resource = Bootstrap::getObjectManager()->get(ResourceConnection::class);
        $resource->getConnection()->delete($resource->getTableName('catalog_product_entity'), ['sku LIKE ?' => self::SKU_PREFIX . '%']);
    }

    private function catalogSize(): int
    {
        $resource = Bootstrap::getObjectManager()->get(ResourceConnection::class);

        return (int) $resource->getConnection()->fetchOne(
            $resource->getConnection()->select()->from($resource->getTableName('catalog_product_entity'), 'COUNT(*)')
        );
    }

    /**
     * @param array<int, array<string, mixed>> $conditions
     */
    private function label(string $name, array $conditions): LabelInterface
    {
        /** @var LabelInterface $label */
        $label = Bootstrap::getObjectManager()->get(LabelFixture::class)->apply([
            'name' => $name,
            'design_id' => (int) DataFixtureStorageManager::getStorage()->get('design')->getDesignId(),
            'apply_to_parent' => true,
            'conditions_serialized' => Bootstrap::getObjectManager()->get(Json::class)->serialize([
                'type' => Combine::class, 'aggregator' => 'all', 'value' => '1', 'conditions' => $conditions,
            ]),
        ]);

        return $label;
    }
}
