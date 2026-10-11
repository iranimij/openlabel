<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Test\Integration\Controller\Adminhtml\Label;

use Iranimij\OpenLabel\Api\LabelRepositoryInterface;
use Iranimij\OpenLabel\Model\Condition\IsNew;
use Iranimij\OpenLabel\Model\Label\QuickConditions;
use Iranimij\OpenLabel\Model\ResourceModel\Label\CollectionFactory;
use Iranimij\OpenLabel\Model\Rule\Condition\Combine;
use Iranimij\OpenLabel\Model\Rule\Condition\Product;
use Iranimij\OpenLabel\Test\Fixture\Design as DesignFixture;
use Iranimij\OpenLabel\Test\Fixture\Label as LabelFixture;
use Iranimij\OpenLabel\Test\Fixture\ScheduledSearchIndex;
use Iranimij\OpenLabel\Ui\DataProvider\Label\Form;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\TestCase\AbstractBackendController;

/**
 * "Show when": quick toggles and the advanced rule tree saved through the form.
 *
 * @magentoAppArea adminhtml
 */
#[DbIsolation(false)]
class ShowWhenTest extends AbstractBackendController
{
    /**
     * @var string
     */
    protected $resource = 'Iranimij_OpenLabel::labels';

    /**
     * @var string
     */
    protected $uri = 'backend/openlabel/label/newConditionHtml';

    protected function tearDown(): void
    {
        $repository = $this->_objectManager->create(LabelRepositoryInterface::class);
        foreach ($this->_objectManager->create(CollectionFactory::class)->create()
            ->addFieldToFilter('name', ['in' => ['Quick new', 'Both']])->getAllIds() as $id) {
            $repository->deleteById((int) $id);
        }
        parent::tearDown();
    }

    #[DataFixture(ScheduledSearchIndex::class)]
    #[DataFixture(ProductFixture::class, ['sku' => 'ol-quick-new'], 'product')]
    #[DataFixture(ProductFixture::class, ['sku' => 'ol-quick-old'], 'old')]
    #[DataFixture(DesignFixture::class, [], 'design')]
    #[DataFixture(LabelFixture::class, ['design_id' => '$design.design_id$', 'name' => 'Raw tree'], 'raw')]
    public function testQuickToggleIndexesTheSameProductsAsTheEquivalentTree(): void
    {
        $old = (int) DataFixtureStorageManager::getStorage()->get('old')->getId();
        $connection = $this->_objectManager->get(ResourceConnection::class)->getConnection();
        $connection->update(
            $connection->getTableName('catalog_product_entity'),
            ['created_at' => '2020-01-01 00:00:00'],
            ['entity_id = ?' => $old]
        );
        $raw = DataFixtureStorageManager::getStorage()->get('raw');
        $repository = $this->_objectManager->create(LabelRepositoryInterface::class);
        $label = $repository->getById((int) $raw->getLabelId());
        $label->setConditionsSerialized($this->json()->serialize([
            'type' => Combine::class, 'aggregator' => 'all', 'value' => '1', 'conditions' => [
                ['type' => IsNew::class, 'attribute' => 'days_since_created', 'operator' => '<=', 'value' => '30'],
            ],
        ]));
        $repository->save($label);

        $this->save(['name' => 'Quick new', 'quick' => ['is_new' => '1', 'new_days' => '30']]);

        $quickId = $this->labelId('Quick new');
        $products = $this->indexedProducts($quickId);
        self::assertContains((int) DataFixtureStorageManager::getStorage()->get('product')->getId(), $products);
        self::assertNotContains($old, $products, 'a product created in 2020 is not new');
        self::assertSame($this->indexedProducts((int) $raw->getLabelId()), $products);
    }

    #[DataFixture(ScheduledSearchIndex::class)]
    #[DataFixture(ProductFixture::class, ['sku' => 'ol-quick-a'], 'a')]
    #[DataFixture(ProductFixture::class, ['sku' => 'ol-quick-b'], 'b')]
    #[DataFixture(DesignFixture::class, [], 'design')]
    public function testTogglesAndAdvancedTreeAreSavedTogetherAndCombineWithAnd(): void
    {
        $this->save([
            'name' => 'Both',
            'quick' => ['is_new' => '1', 'new_days' => '30'],
            'rule' => ['conditions' => [
                '1' => ['type' => Combine::class, 'aggregator' => 'all', 'value' => '1', 'new_child' => ''],
                '1--1' => ['type' => Product::class, 'attribute' => 'sku', 'operator' => '==', 'value' => 'ol-quick-a'],
            ]],
        ]);

        $id = $this->labelId('Both');
        [$values, $advanced] = $this->_objectManager->get(QuickConditions::class)->decompose(
            $this->_objectManager->create(LabelRepositoryInterface::class)->getById($id)->getConditionsSerialized()
        );
        self::assertSame('1', $values['is_new']);
        self::assertSame('sku', $advanced['conditions'][0]['attribute']);
        self::assertSame(
            [(int) DataFixtureStorageManager::getStorage()->get('a')->getId()],
            $this->indexedProducts($id),
            'new AND sku = ol-quick-a'
        );
    }

    #[DataFixture(DesignFixture::class, [], 'design')]
    #[DataFixture(LabelFixture::class, [
        'design_id' => '$design.design_id$',
        'conditions_serialized' => '{"type":"Iranimij\\\\OpenLabel\\\\Model\\\\Rule\\\\Condition\\\\Combine","aggregator":"all","value":"1","conditions":[{"type":"Iranimij\\\\OpenLabel\\\\Model\\\\Condition\\\\OnSale","attribute":"discount_percent","operator":">=","value":"25","ol_quick":"on_sale"}],"ol_layout":"quick"}',
    ], 'label')]
    public function testFormShowsSavedTogglesAndRendersTheRuleTree(): void
    {
        $id = (int) DataFixtureStorageManager::getStorage()->get('label')->getLabelId();
        $provider = $this->_objectManager->create(Form::class, [
            'name' => 'openlabel_label_form_data_source', 'primaryFieldName' => 'label_id', 'requestFieldName' => 'id',
        ]);

        $quick = $provider->getData()[$id]['quick'];
        self::assertSame(['1', '25'], [$quick['on_sale'], $quick['on_sale_min']]);

        $this->dispatch('backend/openlabel/label/edit/id/' . $id);
        $body = (string) $this->getResponse()->getBody();
        self::assertStringContainsString('rule_conditions_fieldset', $body);
        // The tree is delivered inside the UI form's JSON config, so its quotes arrive escaped.
        self::assertMatchesRegularExpression('/data-form-part=\\\\?"openlabel_label_form\\\\?"/', $body);
    }

    public function testNewConditionHtmlRendersAnAttributeRow(): void
    {
        $this->dispatch('backend/openlabel/label/newConditionHtml/form/rule_conditions_fieldset/form_namespace/openlabel_label_form/id/1--1/type/'
            . str_replace('\\', '-', Product::class) . '|sku');
        $html = (string) $this->getResponse()->getBody();
        // Element names are entity-escaped by the core form renderer ("]" becomes &#x5D;).
        self::assertMatchesRegularExpression('/rule(\\[|&#x5B;)conditions(\\]|&#x5D;)(\\[|&#x5B;)1--1(\\]|&#x5D;)(\\[|&#x5B;)attribute/', $html);
        self::assertStringContainsString('data-form-part="openlabel_label_form"', $html);
    }

    public function testNewConditionHtmlRefusesClassesOutsideOpenLabel(): void
    {
        $this->dispatch('backend/openlabel/label/newConditionHtml/id/1--1/type/Magento-Framework-DataObject');

        self::assertSame('', (string) $this->getResponse()->getBody());
    }

    /**
     * @param array<string, mixed> $data
     */
    private function save(array $data): void
    {
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setPostValue($data + [
            'status' => '1',
            'design_id' => (string) DataFixtureStorageManager::getStorage()->get('design')->getDesignId(),
            'store_ids' => ['0'],
            'placements' => [['area' => 'listing', 'position' => 'tl']],
        ]);
        $this->dispatch('backend/openlabel/label/save');
        $this->resetRequest();
    }

    private function labelId(string $name): int
    {
        $id = (int) $this->_objectManager->create(CollectionFactory::class)->create()
            ->addFieldToFilter('name', ['eq' => $name])->getFirstItem()->getId();
        self::assertGreaterThan(0, $id, "label $name was saved");

        return $id;
    }

    /**
     * @return int[]
     */
    private function indexedProducts(int $labelId): array
    {
        $connection = $this->_objectManager->get(ResourceConnection::class)->getConnection();
        $ids = $connection->fetchCol(
            $connection->select()->distinct()
                ->from($connection->getTableName('openlabel_index'), 'product_id')
                ->where('label_id = ?', $labelId)
                ->where('store_id = ?', 1)
                ->order('product_id')
        );

        return array_map('intval', $ids);
    }

    private function json(): Json
    {
        return $this->_objectManager->get(Json::class);
    }
}
