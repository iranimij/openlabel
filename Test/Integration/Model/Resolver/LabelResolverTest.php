<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Test\Integration\Model\Resolver;

use Iranimij\OpenLabel\Api\Data\LabelInterface;
use Iranimij\OpenLabel\Api\Data\ResolvedLabelInterface;
use Iranimij\OpenLabel\Api\LabelRepositoryInterface;
use Iranimij\OpenLabel\Api\LabelResolverInterface;
use Iranimij\OpenLabel\Test\Fixture\Design as DesignFixture;
use Iranimij\OpenLabel\Test\Fixture\Label as LabelFixture;
use Iranimij\OpenLabel\Test\Integration\Helper\QueryCounter;
use Iranimij\OpenLabel\ViewModel\Labels;
use Magento\Customer\Model\Context as CustomerContext;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\App\ResourceConnection;
use Magento\Store\Test\Fixture\Store as StoreFixture;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * One SELECT per listing or product page, correct labels per customer group, store view and time window.
 * Uses the sample-data catalog of the integration database as the "listing"; labels without conditions match it all.
 */
#[DbIsolation(false)]
#[DataFixture(StoreFixture::class, ['code' => 'ol_de'], 'store_de')]
#[DataFixture(DesignFixture::class, ['store_texts' => [0 => ['text' => 'Sale'], 1 => ['text' => 'Angebot']]], 'design')]
class LabelResolverTest extends TestCase
{
    private LabelResolverInterface $resolver;
    private LabelRepositoryInterface $labels;
    private QueryCounter $counter;
    /** @var int[] */
    private array $productIds = [];
    /** @var LabelInterface[] */
    private array $created = [];

    protected function setUp(): void
    {
        $om = Bootstrap::getObjectManager();
        $this->resolver = $om->get(LabelResolverInterface::class);
        $this->labels = $om->get(LabelRepositoryInterface::class);
        $this->counter = $om->get(QueryCounter::class);
        $resource = $om->get(ResourceConnection::class);
        $connection = $resource->getConnection();
        $this->productIds = array_map('intval', $connection->fetchCol(
            $connection->select()->from($resource->getTableName('catalog_product_entity'), 'entity_id')
                ->where('type_id = ?', 'simple')->order('entity_id')->limit(36)
        ));
        self::assertCount(36, $this->productIds, 'the integration catalog provides a 36-product listing');
    }

    protected function tearDown(): void
    {
        foreach ($this->created as $label) {
            try {
                $this->labels->deleteById((int) $label->getLabelId());
            } catch (\Exception) {
                // already gone
            }
        }
    }

    public function testAListingOf36ProductsCostsExactlyOneQuery(): void
    {
        $a = $this->label('A', ['priority' => 0]);
        $b = $this->label('B', ['priority' => 1]);
        $c = $this->label('C', ['priority' => 2, 'placements' => [['area' => 'product', 'position' => 'tr']]]);

        $result = [];
        $queries = $this->counter->count(function () use (&$result): void {
            $result = $this->resolver->getForProducts($this->productIds, 1, 0);
        });

        self::assertSame(1, $queries);
        self::assertCount(36, $result);
        $labels = $result[$this->productIds[0]];
        self::assertSame([$a->getLabelId(), $b->getLabelId(), $c->getLabelId()], array_map(static fn (ResolvedLabelInterface $l) => $l->getLabelId(), $labels));
        self::assertSame('listing', $labels[0]->getPlacement()->getArea());
        self::assertSame('tr', $labels[2]->getPlacement()->getPosition());
        self::assertSame('Angebot', $labels[0]->getText(), 'text resolved for the store view');
        self::assertSame('Angebot', $labels[0]->getDesign()->getText(), 'the design of a resolved label is store-resolved too');
        self::assertSame($this->productIds[0], $labels[0]->getProductId());
    }

    public function testAProductPageCostsOneQuery(): void
    {
        $this->label('A');

        $queries = $this->counter->count(function (): void {
            $this->resolver->getForProducts([$this->productIds[5]], 1, 0);
        });

        self::assertSame(1, $queries);
    }

    public function testHideLowerPriorityAndMaxLabelsApply(): void
    {
        $a = $this->label('A', ['priority' => 0, 'placements' => [['area' => 'listing', 'position' => 'tl', 'max_labels' => 2]]]);
        $h = $this->label('H', ['priority' => 1, 'hide_lower_priority' => true]);
        $this->label('B', ['priority' => 2]);
        $this->label('D', ['priority' => 1]);

        $labels = $this->resolver->getForProducts([$this->productIds[0]], 1, 0)[$this->productIds[0]];

        self::assertSame([$a->getLabelId(), $h->getLabelId()], array_map(static fn (ResolvedLabelInterface $l) => $l->getLabelId(), $labels));
    }

    public function testCustomerGroupRestrictionsAreHonouredWithoutPriceConditions(): void
    {
        $wholesale = $this->label('Wholesale only', ['customer_group_ids' => [2]]);

        self::assertSame([], $this->resolver->getForProducts([$this->productIds[0]], 1, 0));
        $labels = $this->resolver->getForProducts([$this->productIds[0]], 1, 2)[$this->productIds[0]];
        self::assertSame([$wholesale->getLabelId()], array_map(static fn (ResolvedLabelInterface $l) => $l->getLabelId(), $labels));
    }

    public function testDisabledLabelsAndClosedWindowsAreNotResolved(): void
    {
        $this->label('Off', ['status' => LabelInterface::STATUS_DISABLED]);
        $this->label('Over', ['valid_to' => gmdate('Y-m-d H:i:s', time() - 3600)]);
        $this->label('Soon', ['valid_from' => gmdate('Y-m-d H:i:s', time() + 3600)]);
        $now = $this->label('Now', ['valid_from' => gmdate('Y-m-d H:i:s', time() - 3600), 'valid_to' => gmdate('Y-m-d H:i:s', time() + 3600)]);

        $labels = $this->resolver->getForProducts([$this->productIds[0]], 1, 0)[$this->productIds[0]] ?? [];

        self::assertSame([$now->getLabelId()], array_map(static fn (ResolvedLabelInterface $l) => $l->getLabelId(), $labels));
    }

    public function testStoreTextFallsBackToTheDefaultOnOtherStoreViews(): void
    {
        $this->label('A');
        $storeDe = (int) DataFixtureStorageManager::getStorage()->get('store_de')->getId();

        $labels = $this->resolver->getForProducts([$this->productIds[0]], $storeDe, 0)[$this->productIds[0]];

        self::assertSame('Sale', $labels[0]->getText());
    }

    public function testViewModelMemoizesAndReadsTheGroupFromTheHttpContext(): void
    {
        $wholesale = $this->label('Wholesale only', ['customer_group_ids' => [2]]);
        $om = Bootstrap::getObjectManager();
        $om->get(HttpContext::class)->setValue(CustomerContext::CONTEXT_GROUP, 2, 0);
        /** @var Labels $viewModel */
        $viewModel = $om->create(Labels::class);

        $queries = $this->counter->count(function () use ($viewModel): void {
            $viewModel->getForProducts($this->productIds);
            $viewModel->getForProduct($this->productIds[3]);
            $viewModel->getStacks($this->productIds[3], 'listing');
        });

        self::assertSame(1, $queries, 'one query for the listing, memoized afterwards');
        $stacks = $viewModel->getStacks($this->productIds[3], 'listing');
        self::assertSame(['tl'], array_keys($stacks));
        self::assertSame($wholesale->getLabelId(), $stacks['tl'][0]->getLabelId());
        self::assertSame([], $viewModel->getStacks($this->productIds[3], 'product'));
        $om->get(HttpContext::class)->setValue(CustomerContext::CONTEXT_GROUP, 0, 0);
    }

    #[Config('openlabel/general/enabled', '0', 'store', 'default')]
    public function testDisabledModuleResolvesNothingWithoutAQuery(): void
    {
        $this->label('A');
        /** @var Labels $viewModel */
        $viewModel = Bootstrap::getObjectManager()->create(Labels::class);

        $result = [];
        $queries = $this->counter->count(function () use ($viewModel, &$result): void {
            $result = $viewModel->getForProducts($this->productIds);
        });

        self::assertFalse($viewModel->isEnabled());
        self::assertSame(0, $queries);
        self::assertSame([], $result);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function label(string $name, array $data = []): LabelInterface
    {
        /** @var LabelInterface $label */
        $label = Bootstrap::getObjectManager()->get(LabelFixture::class)->apply(array_merge([
            'name' => $name,
            'design_id' => (int) DataFixtureStorageManager::getStorage()->get('design')->getDesignId(),
        ], $data));
        $this->created[] = $label;

        return $label;
    }
}
