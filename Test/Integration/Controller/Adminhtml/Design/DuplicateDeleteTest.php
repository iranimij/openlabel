<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Test\Integration\Controller\Adminhtml\Design;

use Iranimij\OpenLabel\Api\DesignRepositoryInterface;
use Iranimij\OpenLabel\Model\ResourceModel\Design\CollectionFactory;
use Iranimij\OpenLabel\Test\Fixture\Design as DesignFixture;
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
class DuplicateDeleteTest extends AbstractBackendController
{
    /**
     * @var string
     */
    protected $resource = 'Iranimij_OpenLabel::designs';

    /**
     * @var string
     */
    protected $uri = 'backend/openlabel/design/duplicate';

    /**
     * @var string
     */
    protected $httpMethod = HttpRequest::METHOD_POST;

    public function testDuplicatingABuiltInDesignGivesAnEditableCopy(): void
    {
        $system = $this->repository()->getById($this->systemDesignId());

        $this->postTo('backend/openlabel/design/duplicate', ['id' => (string) $system->getDesignId()]);

        $copyId = (int) $this->_objectManager->create(CollectionFactory::class)->create()
            ->addFieldToFilter('name', ['eq' => $system->getName() . ' (copy)'])->getFirstItem()->getId();
        self::assertGreaterThan(0, $copyId);
        $copy = $this->repository()->getById($copyId);
        self::assertFalse($copy->isSystem());
        self::assertSame($system->getText(), $copy->getText());
        self::assertSame($system->getBgColor(), $copy->getBgColor());
        self::assertRedirect(self::stringContains('openlabel/design/edit/id/' . $copyId));
    }

    public function testBuiltInDesignCannotBeDeleted(): void
    {
        $id = $this->systemDesignId();

        $this->postTo('backend/openlabel/design/delete', ['id' => (string) $id]);

        self::assertSame($id, $this->repository()->getById($id)->getDesignId());
        $this->assertSessionMessages(
            self::containsEqual('Built-in designs cannot be deleted. Duplicate one to make your own version.'),
            MessageInterface::TYPE_ERROR
        );
    }

    #[DataFixture(DesignFixture::class, ['name' => 'Throwaway'], 'design')]
    public function testOwnDesignIsDeleted(): void
    {
        $id = (int) DataFixtureStorageManager::getStorage()->get('design')->getDesignId();

        $this->postTo('backend/openlabel/design/delete', ['id' => (string) $id]);

        $this->expectException(NoSuchEntityException::class);
        $this->repository()->getById($id);
    }

    /**
     * @param array<string, string> $params
     */
    private function postTo(string $uri, array $params): void
    {
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setPostValue($params);
        $this->dispatch($uri);
    }

    private function systemDesignId(): int
    {
        return (int) $this->_objectManager->create(CollectionFactory::class)->create()
            ->addFieldToFilter('is_system', ['eq' => 1])->getFirstItem()->getId();
    }

    private function repository(): DesignRepositoryInterface
    {
        return $this->_objectManager->create(DesignRepositoryInterface::class);
    }
}
