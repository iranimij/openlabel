<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Test\Integration\Controller\Adminhtml\Label;

use Iranimij\OpenLabel\Api\Data\LabelInterface;
use Iranimij\OpenLabel\Api\LabelRepositoryInterface;
use Iranimij\OpenLabel\Model\ResourceModel\Label\CollectionFactory;
use Iranimij\OpenLabel\Test\Fixture\Design as DesignFixture;
use Iranimij\OpenLabel\Test\Fixture\Label as LabelFixture;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\TestFramework\Fixture\Config as ConfigFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\TestCase\AbstractBackendController;

/**
 * @magentoAppArea adminhtml
 * @magentoDbIsolation enabled
 */
class SaveTest extends AbstractBackendController
{
    /**
     * @var string
     */
    protected $resource = 'Iranimij_OpenLabel::labels';

    /**
     * @var string
     */
    protected $uri = 'backend/openlabel/label/save';

    /**
     * @var string
     */
    protected $httpMethod = HttpRequest::METHOD_POST;

    #[ConfigFixture('general/locale/timezone', 'Europe/Berlin')]
    #[DataFixture(DesignFixture::class, [], 'design')]
    public function testNewLabelIsSavedInUtcAndIndexed(): void
    {
        $this->post($this->form('Winter sale') + ['back' => 'edit']);

        $label = $this->labelNamed('Winter sale');
        self::assertSame('2026-11-01 07:00:00', $label->getValidFrom());
        self::assertSame('2026-11-30 22:59:59', $label->getValidTo());
        self::assertSame([1], $label->getStoreIds());
        self::assertSame([0, 1], $label->getCustomerGroupIds());
        self::assertCount(2, $label->getPlacements());
        self::assertSame('product', $label->getPlacements()[1]->getArea());
        self::assertRedirect(self::stringContains('openlabel/label/edit/id/' . $label->getLabelId()));
        self::assertGreaterThan(0, $this->indexRows((int) $label->getLabelId()), 'saving reindexes the label');
    }

    #[DataFixture(DesignFixture::class, [], 'design')]
    public function testAllStoreViewsIsStoredAsNoRestriction(): void
    {
        $data = $this->form('Everywhere');
        $data['store_ids'] = ['0'];

        $this->post($data);

        self::assertSame([], $this->labelNamed('Everywhere')->getStoreIds());
        self::assertRedirect(self::stringContains('openlabel/label/index'));
    }

    #[DataFixture(DesignFixture::class, [], 'design')]
    #[DataFixture(LabelFixture::class, ['design_id' => '$design.design_id$', 'name' => 'Original'], 'label')]
    public function testSaveAndDuplicateOpensADisabledCopy(): void
    {
        $id = (int) DataFixtureStorageManager::getStorage()->get('label')->getLabelId();

        $this->post(['label_id' => (string) $id] + $this->form('Original') + ['back' => 'duplicate']);

        $copy = $this->labelNamed('Original (copy)');
        self::assertSame(LabelInterface::STATUS_DISABLED, $copy->getStatus());
        self::assertCount(2, $copy->getPlacements());
        self::assertRedirect(self::stringContains('openlabel/label/edit/id/' . $copy->getLabelId()));
    }

    #[DataFixture(DesignFixture::class, [], 'design')]
    #[DataFixture(LabelFixture::class, ['design_id' => '$design.design_id$'], 'label')]
    public function testDelete(): void
    {
        $id = (int) DataFixtureStorageManager::getStorage()->get('label')->getLabelId();

        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setPostValue(['id' => (string) $id]);
        $this->dispatch('backend/openlabel/label/delete');

        $this->expectException(NoSuchEntityException::class);
        $this->_objectManager->create(LabelRepositoryInterface::class)->getById($id);
    }

    /**
     * @return array<string, mixed>
     */
    private function form(string $name): array
    {
        return [
            'name' => $name, 'status' => '1', 'priority' => '2',
            'design_id' => (string) DataFixtureStorageManager::getStorage()->get('design')->getDesignId(),
            'apply_to_parent' => '0', 'hide_lower_priority' => '0',
            'store_ids' => ['1'], 'customer_group_ids' => ['0', '1'],
            'valid_from' => '2026-11-01 08:00:00', 'valid_to' => '2026-11-30',
            'placements' => [
                ['area' => 'listing', 'position' => 'tr', 'max_labels' => '3', 'stacking' => 'vertical', 'gap' => '4'],
                ['area' => 'product', 'position' => 'tl', 'max_labels' => '3', 'stacking' => 'vertical', 'gap' => '4'],
            ],
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    private function post(array $data): void
    {
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setPostValue($data);
        $this->dispatch($this->uri);
    }

    private function labelNamed(string $name): LabelInterface
    {
        $id = (int) $this->_objectManager->create(CollectionFactory::class)->create()
            ->addFieldToFilter('name', ['eq' => $name])->getFirstItem()->getId();
        self::assertGreaterThan(0, $id, "label $name was saved");

        return $this->_objectManager->create(LabelRepositoryInterface::class)->getById($id);
    }

    private function indexRows(int $labelId): int
    {
        $connection = $this->_objectManager->get(ResourceConnection::class)->getConnection();

        return (int) $connection->fetchOne(
            $connection->select()->from($connection->getTableName('openlabel_index'), 'COUNT(*)')
                ->where('label_id = ?', $labelId)
        );
    }
}
