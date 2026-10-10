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
class MassDeleteTest extends AbstractBackendController
{
    /**
     * @var string
     */
    protected $resource = 'Iranimij_OpenLabel::designs';

    /**
     * @var string
     */
    protected $uri = 'backend/openlabel/design/massDelete';

    /**
     * @var string
     */
    protected $httpMethod = HttpRequest::METHOD_POST;

    #[DataFixture(DesignFixture::class, ['name' => 'Unused'], 'unused')]
    #[DataFixture(DesignFixture::class, ['name' => 'Used'], 'used')]
    #[DataFixture(LabelFixture::class, ['design_id' => '$used.design_id$'], 'label')]
    public function testDeletesUnusedDesignsAndKeepsSystemAndUsedOnes(): void
    {
        $storage = DataFixtureStorageManager::getStorage();
        $unused = (int) $storage->get('unused')->getDesignId();
        $used = (int) $storage->get('used')->getDesignId();
        $system = (int) $this->_objectManager->create(CollectionFactory::class)->create()
            ->addFieldToFilter('is_system', ['eq' => 1])->getFirstItem()->getId();

        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setPostValue(['selected' => [$unused, $used, $system], 'namespace' => 'openlabel_design_listing']);
        $this->dispatch($this->uri);

        $repository = $this->_objectManager->create(DesignRepositoryInterface::class);
        self::assertSame($used, $repository->getById($used)->getDesignId());
        self::assertSame($system, $repository->getById($system)->getDesignId());
        $this->assertSessionMessages(self::containsEqual('1 design(s) deleted.'), MessageInterface::TYPE_SUCCESS);
        $this->assertSessionMessages(
            self::containsEqual('Built-in designs cannot be deleted. Duplicate one to make your own version.'),
            MessageInterface::TYPE_ERROR
        );
        $this->expectException(NoSuchEntityException::class);
        $repository->getById($unused);
    }
}
