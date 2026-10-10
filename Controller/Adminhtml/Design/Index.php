<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Controller\Adminhtml\Design;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\View\Result\PageFactory;

/**
 * Catalog › OpenLabel › Designs: reusable looks shared by labels.
 */
class Index extends Action implements HttpGetActionInterface
{
    public const ADMIN_RESOURCE = 'Iranimij_OpenLabel::designs';

    /**
     * @param Context $context
     * @param PageFactory $pageFactory
     */
    public function __construct(
        Context $context,
        private readonly PageFactory $pageFactory
    ) {
        parent::__construct($context);
    }

    /**
     * @inheritDoc
     */
    public function execute(): Page
    {
        /** @var Page $page */
        $page = $this->pageFactory->create();
        $page->setActiveMenu('Iranimij_OpenLabel::designs');
        $page->getConfig()->getTitle()->prepend((string) __('Designs'));

        return $page;
    }
}
