<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Test\Integration\Console;

use Iranimij\OpenLabel\Api\Data\LabelInterface;
use Iranimij\OpenLabel\Api\LabelRepositoryInterface;
use Iranimij\OpenLabel\Console\Preview;
use Iranimij\OpenLabel\Console\Reindex;
use Iranimij\OpenLabel\Model\Condition\OnSale;
use Iranimij\OpenLabel\Model\Indexer\IndexReader;
use Iranimij\OpenLabel\Model\Rule\Condition\Combine;
use Iranimij\OpenLabel\Test\Fixture\Design as DesignFixture;
use Iranimij\OpenLabel\Test\Fixture\Label as LabelFixture;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Framework\App\ResourceConnection;
use Iranimij\OpenLabel\Test\Integration\Helper\StockSetter;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

#[DbIsolation(false)]
#[DataFixture(ProductFixture::class, ['sku' => 'ol-cli-sale', 'price' => 100, 'special_price' => 80], 'sale')]
#[DataFixture(ProductFixture::class, ['sku' => 'ol-cli-full', 'price' => 100], 'full')]
#[DataFixture(DesignFixture::class, [], 'design')]
class CommandsTest extends TestCase
{
    private ?LabelInterface $label = null;

    protected function setUp(): void
    {
        Bootstrap::getObjectManager()->get(StockSetter::class)->reindex();
        $this->label = Bootstrap::getObjectManager()->get(LabelFixture::class)->apply([
            'name' => 'CLI sale',
            'design_id' => (int) DataFixtureStorageManager::getStorage()->get('design')->getDesignId(),
            'conditions_serialized' => Bootstrap::getObjectManager()->get(Json::class)->serialize([
                'type' => Combine::class, 'aggregator' => 'all', 'value' => '1',
                'conditions' => [['type' => OnSale::class, 'attribute' => 'on_sale', 'operator' => '==', 'value' => '1']],
            ]),
        ]);
    }

    protected function tearDown(): void
    {
        if ($this->label !== null) {
            Bootstrap::getObjectManager()->get(LabelRepositoryInterface::class)->deleteById((int) $this->label->getLabelId());
        }
    }

    public function testPreviewListsMatchedSkus(): void
    {
        $tester = new CommandTester(Bootstrap::getObjectManager()->get(Preview::class));

        $exit = $tester->execute(['label_id' => (string) $this->label->getLabelId(), '--store' => '1']);

        self::assertSame(Command::SUCCESS, $exit);
        // A catalog with sample data has hundreds of products on sale; a bare one has only the fixture product.
        self::assertMatchesRegularExpression('/matches [\d,]+ products in store view "default" \(1\)\./', $tester->getDisplay());
        self::assertMatchesRegularExpression('/First \d+ SKUs:/', $tester->getDisplay());
        self::assertStringNotContainsString('ol-cli-full', $tester->getDisplay());
        $skus = Bootstrap::getObjectManager()->get(IndexReader::class)->skus((int) $this->label->getLabelId(), 1, 100000);
        self::assertContains('ol-cli-sale', $skus);
        self::assertNotContains('ol-cli-full', $skus);
    }

    public function testLabelReindexRebuildsDeletedRows(): void
    {
        $resource = Bootstrap::getObjectManager()->get(ResourceConnection::class);
        $resource->getConnection()->delete($resource->getTableName('openlabel_index'), ['label_id = ?' => $this->label->getLabelId()]);
        $tester = new CommandTester(Bootstrap::getObjectManager()->get(Reindex::class));

        $exit = $tester->execute(['label_id' => (string) $this->label->getLabelId()]);

        self::assertSame(Command::SUCCESS, $exit);
        self::assertStringContainsString('Label #' . $this->label->getLabelId() . ' "CLI sale"', $tester->getDisplay());
        self::assertSame(1, (int) $resource->getConnection()->fetchOne(
            $resource->getConnection()->select()->from($resource->getTableName('openlabel_index'), 'COUNT(DISTINCT product_id)')
                ->where('label_id = ?', $this->label->getLabelId())
                ->where('product_id IN (?)', [DataFixtureStorageManager::getStorage()->get('sale')->getId(), DataFixtureStorageManager::getStorage()->get('full')->getId()])
        ));
    }

    public function testFullReindexRunsThroughTheIndexer(): void
    {
        $tester = new CommandTester(Bootstrap::getObjectManager()->get(Reindex::class));

        $exit = $tester->execute([]);

        self::assertSame(Command::SUCCESS, $exit);
        self::assertStringContainsString('Full reindex done', $tester->getDisplay());
        self::assertTrue(Bootstrap::getObjectManager()->get(IndexerRegistry::class)->get('openlabel_product')->isValid());
    }
}
