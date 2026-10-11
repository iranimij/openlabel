<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Test\Integration\Ui;

use Iranimij\OpenLabel\Model\ResourceModel\Label\Grid\Collection;
use Iranimij\OpenLabel\Test\Fixture\Design as DesignFixture;
use Iranimij\OpenLabel\Test\Fixture\Label as LabelFixture;
use Magento\Framework\App\ResourceConnection;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @magentoAppArea adminhtml
 */
#[DbIsolation(true)]
class LabelGridTest extends TestCase
{
    #[DataFixture(DesignFixture::class, ['name' => 'Red'], 'design')]
    #[DataFixture(LabelFixture::class, [
        'design_id' => '$design.design_id$',
        'placements' => [['area' => 'listing', 'position' => 'tr'], ['area' => 'product', 'position' => 'tl']],
    ], 'label')]
    public function testRowCarriesMatchedCountPlacementsAndDesign(): void
    {
        $id = (int) DataFixtureStorageManager::getStorage()->get('label')->getLabelId();
        $this->writeIndexRows($id, [11, 11, 12, 13]);

        $row = $this->row($id);

        self::assertSame('3', (string) $row['matched_count'], 'distinct products, not index rows');
        self::assertSame('listing:tr,product:tl', $row['placements'], 'in placement order');
        self::assertSame('Red', $row['design_name']);
        self::assertSame('Sale', $row['design_text']);
    }

    #[DataFixture(DesignFixture::class, [], 'design')]
    #[DataFixture(LabelFixture::class, ['design_id' => '$design.design_id$'], 'always')]
    #[DataFixture(LabelFixture::class, ['design_id' => '$design.design_id$', 'valid_to' => '2020-01-01 00:00:00'], 'ended')]
    #[DataFixture(LabelFixture::class, ['design_id' => '$design.design_id$', 'status' => 0], 'off')]
    public function testActiveNowFilter(): void
    {
        $storage = DataFixtureStorageManager::getStorage();
        $mine = [
            (int) $storage->get('always')->getLabelId(),
            (int) $storage->get('ended')->getLabelId(),
            (int) $storage->get('off')->getLabelId(),
        ];

        $active = $this->collection()->addFieldToFilter('active_now', '1')->addFieldToFilter('main_table.label_id', ['in' => $mine]);
        $inactive = $this->collection()->addFieldToFilter('active_now', '0')->addFieldToFilter('main_table.label_id', ['in' => $mine]);

        self::assertSame([$mine[0]], array_map('intval', $active->getColumnValues('label_id')));
        self::assertEqualsCanonicalizing([$mine[1], $mine[2]], array_map('intval', $inactive->getColumnValues('label_id')));
        self::assertSame(1, $active->getSize());
    }

    /**
     * @return array<string, mixed>
     */
    private function row(int $labelId): array
    {
        $collection = $this->collection()->addFieldToFilter('main_table.label_id', $labelId);
        $items = $collection->getData();
        self::assertCount(1, $items);

        return $items[0];
    }

    private function collection(): Collection
    {
        return Bootstrap::getObjectManager()->create(Collection::class);
    }

    /**
     * @param int[] $productIds
     */
    private function writeIndexRows(int $labelId, array $productIds): void
    {
        $connection = Bootstrap::getObjectManager()->get(ResourceConnection::class)->getConnection();
        $table = $connection->getTableName('openlabel_index');
        $connection->delete($table, ['label_id = ?' => $labelId]);
        $group = -1;
        foreach ($productIds as $productId) {
            $connection->insert($table, [
                'label_id' => $labelId, 'product_id' => $productId, 'store_id' => 1,
                'customer_group_id' => $group--, 'priority' => 0,
            ]);
        }
    }
}
