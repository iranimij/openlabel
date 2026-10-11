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
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\TestCase\AbstractBackendController;

/**
 * "Show where": editing, removing and validating placements through the form.
 *
 * @magentoAppArea adminhtml
 * @magentoDbIsolation enabled
 */
class PlacementsTest extends AbstractBackendController
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

    #[DataFixture(DesignFixture::class, ['name' => 'Default look'], 'design')]
    #[DataFixture(DesignFixture::class, ['name' => 'Big gallery badge'], 'big')]
    #[DataFixture(LabelFixture::class, [
        'design_id' => '$design.design_id$',
        'placements' => [['area' => 'listing', 'position' => 'tl'], ['area' => 'product', 'position' => 'tr']],
    ], 'label')]
    public function testPlacementIsEditedAndAnotherRemoved(): void
    {
        $storage = DataFixtureStorageManager::getStorage();
        $id = (int) $storage->get('label')->getLabelId();

        $this->post('backend/openlabel/label/save', $this->form($id, [
            ['area' => 'product', 'position' => 'br', 'design_id' => (string) $storage->get('big')->getDesignId(),
                'stacking' => 'horizontal', 'gap' => '8', 'offset_x' => '-4', 'offset_y' => '6',
                'max_labels' => '2', 'pin_physical_side' => '1'],
            ['area' => 'listing', 'position' => 'tl', 'delete' => 'true'],
        ]));

        $placements = $this->_objectManager->create(LabelRepositoryInterface::class)->getById($id)->getPlacements();
        self::assertCount(1, $placements);
        $placement = $placements[0];
        self::assertSame(
            ['product', 'br', (int) $storage->get('big')->getDesignId(), 'horizontal', 8, -4, 6, 2, true],
            [$placement->getArea(), $placement->getPosition(), $placement->getDesignId(), $placement->getStacking(),
                $placement->getGap(), $placement->getOffsetX(), $placement->getOffsetY(), $placement->getMaxLabels(),
                $placement->isPinPhysicalSide()]
        );
    }

    #[DataFixture(DesignFixture::class, [], 'design')]
    public function testOutOfRangeValuesAreReportedBeforeSaving(): void
    {
        $this->post('backend/openlabel/label/validate', $this->form(0, [
            ['area' => 'listing', 'position' => 'tl', 'max_labels' => '20', 'gap' => '100', 'offset_x' => '500'],
        ]));

        $result = $this->_objectManager->get(Json::class)->unserialize((string) $this->getResponse()->getBody());
        self::assertTrue($result['error']);
        self::assertContains('Max labels must be between 1 and 10.', $result['messages']);
        self::assertContains('Gap must be between 0 and 64 px.', $result['messages']);
        self::assertContains('Offsets must be between -200 and 200 px.', $result['messages']);
    }

    /**
     * @param array<int, array<string, string>> $placements
     * @return array<string, mixed>
     */
    private function form(int $id, array $placements): array
    {
        return array_filter([
            'label_id' => $id > 0 ? (string) $id : null,
            'name' => 'Placements', 'status' => '1',
            'design_id' => (string) DataFixtureStorageManager::getStorage()->get('design')->getDesignId(),
            'store_ids' => ['0'],
            'placements' => $placements,
        ], static fn ($value): bool => $value !== null);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function post(string $uri, array $data): void
    {
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setPostValue($data);
        $this->dispatch($uri);
    }
}
