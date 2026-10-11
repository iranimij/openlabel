<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Test\Integration\Controller\Adminhtml\Label;

use Iranimij\OpenLabel\Test\Fixture\Design as DesignFixture;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\TestCase\AbstractBackendController;

/**
 * Inline validation before save (UI form validate_url): errors come back as JSON, nothing is saved.
 *
 * @magentoAppArea adminhtml
 * @magentoDbIsolation enabled
 */
class ValidateTest extends AbstractBackendController
{
    /**
     * @var string
     */
    protected $resource = 'Iranimij_OpenLabel::labels';

    /**
     * @var string
     */
    protected $uri = 'backend/openlabel/label/validate';

    /**
     * @var string
     */
    protected $httpMethod = HttpRequest::METHOD_POST;

    #[DataFixture(DesignFixture::class, [], 'design')]
    public function testMissingPlacementAndBadDatesAreReportedWithInstructions(): void
    {
        $result = $this->validate([
            'name' => 'No where', 'design_id' => $this->designId(),
            'valid_from' => '2026-12-01', 'valid_to' => '2026-11-01',
        ]);

        self::assertTrue($result['error']);
        self::assertContains('Pick at least one placement so the label appears somewhere.', $result['messages']);
        self::assertContains('The end date must be after the start date.', $result['messages']);
    }

    #[DataFixture(DesignFixture::class, [], 'design')]
    public function testUnreadableDateIsReported(): void
    {
        $result = $this->validate(['name' => 'x', 'design_id' => $this->designId(), 'valid_from' => 'soon']);

        self::assertTrue($result['error']);
        self::assertStringContainsString('Enter the date as YYYY-MM-DD', implode(' ', $result['messages']));
    }

    #[DataFixture(DesignFixture::class, [], 'design')]
    public function testValidLabelPasses(): void
    {
        $result = $this->validate([
            'name' => 'Fine', 'design_id' => $this->designId(),
            'placements' => [['area' => 'listing', 'position' => 'tl']],
        ]);

        self::assertFalse($result['error']);
    }

    /**
     * @param array<string, mixed> $data
     * @return array{error: bool, messages: string[]}
     */
    private function validate(array $data): array
    {
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setPostValue($data);
        $this->dispatch($this->uri);

        return $this->_objectManager->get(Json::class)->unserialize((string) $this->getResponse()->getBody());
    }

    private function designId(): string
    {
        return (string) DataFixtureStorageManager::getStorage()->get('design')->getDesignId();
    }
}
