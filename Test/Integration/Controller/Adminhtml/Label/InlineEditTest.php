<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Test\Integration\Controller\Adminhtml\Label;

use Iranimij\OpenLabel\Api\LabelRepositoryInterface;
use Iranimij\OpenLabel\Test\Fixture\Design as DesignFixture;
use Iranimij\OpenLabel\Test\Fixture\Label as LabelFixture;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\TestFramework\Fixture\Config as ConfigFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\TestCase\AbstractBackendController;

/**
 * @magentoAppArea adminhtml
 * @magentoDbIsolation enabled
 */
class InlineEditTest extends AbstractBackendController
{
    /**
     * @var string
     */
    protected $resource = 'Iranimij_OpenLabel::labels';

    /**
     * @var string
     */
    protected $uri = 'backend/openlabel/label/inlineEdit';

    /**
     * @var string
     */
    protected $httpMethod = HttpRequest::METHOD_POST;

    #[ConfigFixture('general/locale/timezone', 'Europe/Berlin')]
    #[DataFixture(DesignFixture::class, [], 'design')]
    #[DataFixture(LabelFixture::class, ['design_id' => '$design.design_id$'], 'label')]
    public function testInlineEditSavesNamePriorityAndShopTimezoneDatesAsUtc(): void
    {
        $id = (int) DataFixtureStorageManager::getStorage()->get('label')->getLabelId();

        $result = $this->edit([$id => [
            'label_id' => (string) $id,
            'name' => 'Black Friday',
            'priority' => '5',
            'status' => '0',
            'valid_from' => '2026-11-01',
            'valid_to' => '2026-11-30',
        ]]);

        self::assertFalse($result['error'], implode(' ', $result['messages']));
        $label = $this->_objectManager->create(LabelRepositoryInterface::class)->getById($id);
        self::assertSame('Black Friday', $label->getName());
        self::assertSame(5, $label->getPriority());
        self::assertSame(0, $label->getStatus());
        self::assertSame('2026-10-31 23:00:00', $label->getValidFrom());
        self::assertSame('2026-11-30 22:59:59', $label->getValidTo(), 'a date-only end runs to the end of the day');
    }

    #[ConfigFixture('general/locale/timezone', 'Europe/Berlin')]
    #[DataFixture(DesignFixture::class, [], 'design')]
    #[DataFixture(
        LabelFixture::class,
        ['design_id' => '$design.design_id$', 'valid_from' => '2026-10-01 14:30:00', 'valid_to' => '2027-01-01 18:15:00'],
        'label'
    )]
    public function testUnchangedDayKeepsTheTimeOfTheSchedule(): void
    {
        $id = (int) DataFixtureStorageManager::getStorage()->get('label')->getLabelId();

        // The grid's date editor posts the day only, in the admin locale, for every editable column of the row.
        $result = $this->edit([$id => [
            'label_id' => (string) $id,
            'status' => '0',
            'valid_from' => '10/01/2026',
            'valid_to' => '01/01/2027',
        ]]);

        self::assertFalse($result['error'], implode(' ', $result['messages']));
        $label = $this->_objectManager->create(LabelRepositoryInterface::class)->getById($id);
        self::assertSame('2026-10-01 14:30:00', $label->getValidFrom());
        self::assertSame('2027-01-01 18:15:00', $label->getValidTo());
    }

    #[DataFixture(DesignFixture::class, [], 'design')]
    #[DataFixture(LabelFixture::class, ['design_id' => '$design.design_id$', 'name' => 'Keep me'], 'label')]
    public function testInvalidValueReportsTheValidationMessageAndKeepsTheLabel(): void
    {
        $id = (int) DataFixtureStorageManager::getStorage()->get('label')->getLabelId();

        $result = $this->edit([$id => ['label_id' => (string) $id, 'name' => '']]);

        self::assertTrue($result['error']);
        self::assertStringContainsString('Enter a name', implode(' ', $result['messages']));
        self::assertSame('Keep me', $this->_objectManager->create(LabelRepositoryInterface::class)->getById($id)->getName());
    }

    /**
     * @param array<int, array<string, string>> $items
     * @return array{messages: string[], error: bool}
     */
    private function edit(array $items): array
    {
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setPostValue(['items' => $items, 'isAjax' => 'true']);
        $this->dispatch($this->uri);

        return $this->_objectManager->get(Json::class)->unserialize((string) $this->getResponse()->getBody());
    }
}
