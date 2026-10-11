<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Test\Integration\Controller\Adminhtml\Starter;

use Iranimij\OpenLabel\Api\Data\LabelInterface;
use Iranimij\OpenLabel\Api\LabelRepositoryInterface;
use Iranimij\OpenLabel\Block\Adminhtml\Label\EmptyState;
use Iranimij\OpenLabel\Model\Label\QuickConditions;
use Iranimij\OpenLabel\Model\ResourceModel\Label\CollectionFactory;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Message\MessageInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\TestFramework\TestCase\AbstractBackendController;

/**
 * One-click starters on the empty labels grid (08 · UX Spec §3, §8).
 *
 * @magentoAppArea adminhtml
 * @magentoDbIsolation enabled
 */
class CreateTest extends AbstractBackendController
{
    /**
     * @var string
     */
    protected $resource = 'Iranimij_OpenLabel::labels';

    /**
     * @var string
     */
    protected $uri = 'backend/openlabel/starter/create';

    /**
     * @var string
     */
    protected $httpMethod = HttpRequest::METHOD_POST;

    public function testSaleStarterCreatesALiveLabelWithTheBuiltInDesign(): void
    {
        $this->start('sale');

        $label = $this->onlyLabel();
        self::assertSame('Sale -{SAVE_PERCENT}%', $label->getName());
        self::assertSame(LabelInterface::STATUS_ENABLED, $label->getStatus());
        self::assertTrue($label->isApplyToParent());
        self::assertSame([['listing', 'tl'], ['product', 'tl']], array_map(
            static fn ($p): array => [$p->getArea(), $p->getPosition()],
            $label->getPlacements()
        ));
        $design = $this->_objectManager->get(ResourceConnection::class)->getConnection()->fetchRow(
            'SELECT name, is_system FROM ' . $this->table('openlabel_design') . ' WHERE design_id = ?',
            [$label->getDesignId()]
        );
        self::assertSame(['name' => 'Sale pill (red)', 'is_system' => '1'], $design);
        [$quick] = $this->_objectManager->get(QuickConditions::class)->decompose($label->getConditionsSerialized());
        self::assertSame('1', $quick['on_sale']);
        self::assertRedirect(self::stringContains('openlabel/label/index'));
        $this->assertSessionMessages(
            self::callback(static fn (array $m): bool => preg_grep('/is live/', $m) !== []),
            MessageInterface::TYPE_SUCCESS
        );
    }

    public function testNewAndLowStockStarters(): void
    {
        $this->start('new');
        $this->resetRequest();
        $this->start('low_stock');

        $names = $this->_objectManager->create(CollectionFactory::class)->create()->getColumnValues('name');
        self::assertEqualsCanonicalizing(['New (30 days)', 'Only {STOCK_QTY} left'], $names);
    }

    public function testDoubleClickCreatesOneLabel(): void
    {
        $this->start('sale');
        $this->resetRequest();
        $this->start('sale');

        self::assertSame(1, $this->_objectManager->create(CollectionFactory::class)->create()->getSize());
    }

    public function testUnknownStarterChangesNothing(): void
    {
        $this->start('bestseller');

        self::assertSame(0, $this->_objectManager->create(CollectionFactory::class)->create()->getSize());
        $this->assertSessionMessages(self::containsEqual('This starter does not exist.'), MessageInterface::TYPE_ERROR);
    }

    public function testEmptyStateShowsTheStartersOnlyWhileThereAreNoLabels(): void
    {
        $html = $this->emptyState();
        self::assertStringContainsString('Sale -{SAVE_PERCENT}%', $html);
        self::assertStringContainsString('New (30 days)', $html);
        self::assertStringContainsString('Only {STOCK_QTY} left', $html);
        self::assertStringContainsString('openlabel/starter/create', $html);
        self::assertStringContainsString('github.com/iranimij/openlabel/discussions', $html);
    }

    public function testEmptyStateIsHiddenOnceALabelExists(): void
    {
        $this->start('new');

        self::assertSame('', trim($this->emptyState()));
    }

    protected function setUp(): void
    {
        parent::setUp();
        // Start from an empty grid inside the test transaction.
        $this->_objectManager->get(ResourceConnection::class)->getConnection()->delete($this->table('openlabel_label'));
    }

    private function start(string $key): void
    {
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setPostValue(['starter' => $key]);
        $this->dispatch($this->uri);
    }

    private function onlyLabel(): LabelInterface
    {
        $ids = $this->_objectManager->create(CollectionFactory::class)->create()->getAllIds();
        self::assertCount(1, $ids);

        return $this->_objectManager->create(LabelRepositoryInterface::class)->getById((int) $ids[0]);
    }

    private function emptyState(): string
    {
        return $this->_objectManager->get(LayoutInterface::class)->createBlock(EmptyState::class)->toHtml();
    }

    private function table(string $name): string
    {
        return $this->_objectManager->get(ResourceConnection::class)->getTableName($name);
    }
}
