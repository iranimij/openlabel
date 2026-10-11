<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Test\Integration\Controller\Adminhtml\Css;

use Iranimij\OpenLabel\Model\Css\Storage;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Filesystem;
use Magento\Framework\Message\MessageInterface;
use Magento\TestFramework\TestCase\AbstractBackendController;

/**
 * "Regenerate CSS" button in Stores › Configuration › Iranimij › OpenLabel.
 *
 * @magentoAppArea adminhtml
 */
class RegenerateTest extends AbstractBackendController
{
    /**
     * @var string
     */
    protected $resource = 'Iranimij_OpenLabel::config';

    /**
     * @var string
     */
    protected $uri = 'backend/openlabel/css/regenerate';

    /**
     * @var string
     */
    protected $httpMethod = HttpRequest::METHOD_POST;

    public function testButtonWritesTheStylesheetAndReturnsToTheSettings(): void
    {
        $this->_objectManager->get(Filesystem::class)->getDirectoryWrite(DirectoryList::MEDIA)->delete(Storage::DIRECTORY);
        $this->_objectManager->get(CacheInterface::class)->remove(Storage::CACHE_KEY_PREFIX . '1');

        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->dispatch($this->uri);

        self::assertNotNull($this->_objectManager->get(Storage::class)->current(1));
        self::assertRedirect(self::stringContains('admin/system_config/edit/section/openlabel'));
        $this->assertSessionMessages(
            self::callback(static fn (array $m): bool => preg_grep('/stylesheet was rebuilt/', $m) !== []),
            MessageInterface::TYPE_SUCCESS
        );
    }
}
