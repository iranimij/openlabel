<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Test\Integration\Controller\Adminhtml\Design;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Filesystem;
use Magento\Framework\Serialize\Serializer\Json;
use Laminas\Stdlib\Parameters;
use Magento\TestFramework\TestCase\AbstractBackendController;

/**
 * @magentoAppArea adminhtml
 */
class UploadTest extends AbstractBackendController
{
    /**
     * @var string
     */
    protected $resource = 'Iranimij_OpenLabel::designs';

    /**
     * @var string
     */
    protected $uri = 'backend/openlabel/design/upload';

    /**
     * @var string
     */
    protected $httpMethod = HttpRequest::METHOD_POST;

    protected function tearDown(): void
    {
        $_FILES = [];
        parent::tearDown();
    }

    public function testPngIsStoredWithItsDimensions(): void
    {
        $image = imagecreatetruecolor(4, 2);
        ob_start();
        imagepng($image);
        $result = $this->upload('badge.png', (string) ob_get_clean(), 'image/png');

        self::assertArrayNotHasKey('error', $result, (string) ($result['error'] ?? ''));
        self::assertMatchesRegularExpression('#^designs/badge(_\d+)?\.png$#', $result['file']);
        self::assertSame([4, 2], [$result['width'], $result['height']]);
        self::assertStringContainsString('/openlabel/designs/', $result['url']);
        self::assertTrue($this->media()->isFile('openlabel/' . $result['file']));
    }

    public function testSvgIsStoredSanitized(): void
    {
        $result = $this->upload(
            'sale.svg',
            '<svg xmlns="http://www.w3.org/2000/svg" width="80" height="24" onload="alert(1)"><script>x()</script><rect width="80" height="24"/></svg>',
            'image/svg+xml'
        );

        self::assertArrayNotHasKey('error', $result, (string) ($result['error'] ?? ''));
        $stored = $this->media()->readFile('openlabel/' . $result['file']);
        self::assertStringNotContainsString('script', $stored);
        self::assertStringNotContainsString('onload', $stored);
        self::assertSame([80, 24], [$result['width'], $result['height']]);
    }

    public function testDisguisedPhpIsRejected(): void
    {
        $result = $this->upload('label.png', '<?php echo "owned";', 'image/png');

        self::assertStringContainsString('does not match its extension', $result['error']);
    }

    /**
     * @return array<string, mixed>
     */
    private function upload(string $name, string $contents, string $type): array
    {
        $tmp = $this->_objectManager->get(Filesystem::class)->getDirectoryWrite(DirectoryList::SYS_TMP);
        $path = $tmp->getAbsolutePath('ol-upload-' . uniqid() . '-' . $name);
        file_put_contents($path, $contents);
        $_FILES['image'] = ['name' => $name, 'type' => $type, 'tmp_name' => $path, 'error' => 0, 'size' => strlen($contents)];
        $this->getRequest()->setFiles(new Parameters($_FILES));
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setParam('param_name', 'image');
        $this->dispatch($this->uri);

        return $this->_objectManager->get(Json::class)->unserialize((string) $this->getResponse()->getBody());
    }

    private function media(): \Magento\Framework\Filesystem\Directory\WriteInterface
    {
        return $this->_objectManager->get(Filesystem::class)->getDirectoryWrite(DirectoryList::MEDIA);
    }
}
