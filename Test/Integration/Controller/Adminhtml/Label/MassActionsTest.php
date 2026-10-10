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
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\MessageInterface;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\TestCase\AbstractBackendController;

/**
 * @magentoAppArea adminhtml
 * @magentoDbIsolation enabled
 */
class MassActionsTest extends AbstractBackendController
{
    /**
     * @var string
     */
    protected $resource = 'Iranimij_OpenLabel::labels';

    /**
     * @var string
     */
    protected $uri = 'backend/openlabel/label/massStatus';

    /**
     * @var string
     */
    protected $httpMethod = HttpRequest::METHOD_POST;

    #[DataFixture(DesignFixture::class, [], 'design')]
    #[DataFixture(LabelFixture::class, ['design_id' => '$design.design_id$'], 'one')]
    #[DataFixture(LabelFixture::class, ['design_id' => '$design.design_id$'], 'two')]
    public function testMassDisable(): void
    {
        $ids = $this->ids('one', 'two');

        $this->post('backend/openlabel/label/massStatus', ['selected' => $ids, 'status' => '0']);

        foreach ($ids as $id) {
            self::assertSame(0, $this->repository()->getById((int) $id)->getStatus());
        }
        $this->assertSessionMessages(
            self::containsEqual('2 label(s) disabled.'),
            MessageInterface::TYPE_SUCCESS
        );
    }

    #[DataFixture(DesignFixture::class, [], 'design')]
    #[DataFixture(LabelFixture::class, ['design_id' => '$design.design_id$', 'status' => 0], 'one')]
    #[DataFixture(LabelFixture::class, ['design_id' => '$design.design_id$', 'status' => 0], 'two')]
    public function testMassEnableOnlyTouchesTheSelection(): void
    {
        $ids = $this->ids('one', 'two');

        $this->post('backend/openlabel/label/massStatus', ['selected' => [$ids[0]], 'status' => '1']);

        self::assertSame(1, $this->repository()->getById((int) $ids[0])->getStatus());
        self::assertSame(0, $this->repository()->getById((int) $ids[1])->getStatus());
    }

    #[DataFixture(DesignFixture::class, [], 'design')]
    #[DataFixture(LabelFixture::class, ['design_id' => '$design.design_id$'], 'one')]
    public function testMassDelete(): void
    {
        [$id] = $this->ids('one');

        $this->post('backend/openlabel/label/massDelete', ['selected' => [$id]]);

        $this->expectException(NoSuchEntityException::class);
        $this->repository()->getById((int) $id);
    }

    /**
     * @param array<string, mixed> $params
     */
    private function post(string $uri, array $params): void
    {
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setPostValue($params + ['namespace' => 'openlabel_label_listing']);
        $this->dispatch($uri);
        self::assertTrue($this->getResponse()->isRedirect());
    }

    /**
     * @return string[]
     */
    private function ids(string ...$names): array
    {
        $storage = DataFixtureStorageManager::getStorage();

        return array_map(static fn (string $n): string => (string) $storage->get($n)->getLabelId(), $names);
    }

    private function repository(): LabelRepositoryInterface
    {
        return $this->_objectManager->create(LabelRepositoryInterface::class);
    }
}
