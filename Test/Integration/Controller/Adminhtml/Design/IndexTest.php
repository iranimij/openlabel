<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Test\Integration\Controller\Adminhtml\Design;

use Magento\TestFramework\TestCase\AbstractBackendController;

/**
 * @magentoAppArea adminhtml
 */
class IndexTest extends AbstractBackendController
{
    /**
     * @var string
     */
    protected $resource = 'Iranimij_OpenLabel::designs';

    /**
     * @var string
     */
    protected $uri = 'backend/openlabel/design/index';

    public function testIndexRendersTheDesignListing(): void
    {
        $this->dispatch($this->uri);

        self::assertSame(200, $this->getResponse()->getHttpResponseCode());
        self::assertStringContainsString('openlabel_design_listing', (string) $this->getResponse()->getBody());
    }
}
