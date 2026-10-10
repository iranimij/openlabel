<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Test\Integration\Controller\Adminhtml\Label;

use Iranimij\OpenLabel\Test\Fixture\Design as DesignFixture;
use Iranimij\OpenLabel\Test\Fixture\Label as LabelFixture;
use Iranimij\OpenLabel\Ui\DataProvider\Label\Form;
use Magento\TestFramework\Fixture\Config as ConfigFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\TestCase\AbstractBackendController;

/**
 * @magentoAppArea adminhtml
 * @magentoDbIsolation enabled
 */
class EditTest extends AbstractBackendController
{
    /**
     * @var string
     */
    protected $resource = 'Iranimij_OpenLabel::labels';

    /**
     * @var string
     */
    protected $uri = 'backend/openlabel/label/new';

    public function testNewLabelFormRendersWithPlainLanguageSections(): void
    {
        $this->dispatch($this->uri);

        $body = (string) $this->getResponse()->getBody();
        self::assertStringContainsString('openlabel_label_form', $body);
    }

    #[ConfigFixture('general/locale/timezone', 'Europe/Berlin')]
    #[DataFixture(DesignFixture::class, [], 'design')]
    #[DataFixture(LabelFixture::class, [
        'design_id' => '$design.design_id$', 'store_ids' => [], 'customer_group_ids' => [1],
        'valid_from' => '2026-07-01 08:00:00', 'placements' => [['area' => 'product', 'position' => 'br', 'gap' => 6]],
    ], 'label')]
    public function testDataProviderShowsShopTimeAllStoresAndPlacementRows(): void
    {
        $id = (int) DataFixtureStorageManager::getStorage()->get('label')->getLabelId();
        $provider = $this->_objectManager->create(Form::class, [
            'name' => 'openlabel_label_form_data_source',
            'primaryFieldName' => 'label_id',
            'requestFieldName' => 'id',
        ]);

        $data = $provider->getData()[$id];

        self::assertSame('2026-07-01 10:00:00', $data['valid_from'], 'UTC shown in the shop timezone');
        self::assertSame(['0'], $data['store_ids'], 'no restriction shows as "All Store Views"');
        self::assertSame(['1'], $data['customer_group_ids']);
        self::assertSame('product', $data['placements'][0]['area']);
        self::assertSame('br', $data['placements'][0]['position']);
        self::assertSame('6', (string) $data['placements'][0]['gap']);
        self::assertSame('1', (string) $data['status']);
    }

    #[DataFixture(DesignFixture::class, [], 'design')]
    #[DataFixture(LabelFixture::class, ['design_id' => '$design.design_id$'], 'label')]
    public function testExistingLabelRendersItsName(): void
    {
        $label = DataFixtureStorageManager::getStorage()->get('label');

        $this->dispatch('backend/openlabel/label/edit/id/' . $label->getLabelId());

        self::assertStringContainsString((string) $label->getName(), (string) $this->getResponse()->getBody());
    }
}
