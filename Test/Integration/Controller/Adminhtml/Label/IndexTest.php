<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Test\Integration\Controller\Adminhtml\Label;

use Magento\Backend\Model\Menu\Config as MenuConfig;
use Magento\TestFramework\TestCase\AbstractBackendController;

/**
 * @magentoAppArea adminhtml
 */
class IndexTest extends AbstractBackendController
{
    /**
     * @var string
     */
    protected $resource = 'Iranimij_OpenLabel::labels';

    /**
     * @var string
     */
    protected $uri = 'backend/openlabel/label/index';

    public function testIndexRendersTheLabelListing(): void
    {
        $this->dispatch($this->uri);

        self::assertSame(200, $this->getResponse()->getHttpResponseCode());
        self::assertStringContainsString('openlabel_label_listing', (string) $this->getResponse()->getBody());
    }

    public function testMenuHasLabelsAndDesignsUnderCatalog(): void
    {
        $menu = $this->_objectManager->get(MenuConfig::class)->getMenu();

        $labels = $menu->get('Iranimij_OpenLabel::labels');
        $designs = $menu->get('Iranimij_OpenLabel::designs');
        self::assertNotNull($labels);
        self::assertNotNull($designs);
        self::assertSame('openlabel/label/index', $labels->getAction());
        self::assertSame('openlabel/design/index', $designs->getAction());
        self::assertNotNull($menu->get('Magento_Catalog::catalog')->getChildren()->get('Iranimij_OpenLabel::openlabel'));
    }
}
