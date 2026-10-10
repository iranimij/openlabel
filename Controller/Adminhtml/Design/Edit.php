<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Controller\Adminhtml\Design;

use Iranimij\OpenLabel\Api\DesignRepositoryInterface;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Page;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Result\PageFactory;

/**
 * Design form (new or existing).
 */
class Edit extends Action implements HttpGetActionInterface
{
    public const ADMIN_RESOURCE = 'Iranimij_OpenLabel::designs';

    /**
     * @param Context $context
     * @param PageFactory $pageFactory
     * @param DesignRepositoryInterface $designRepository
     */
    public function __construct(
        Context $context,
        private readonly PageFactory $pageFactory,
        private readonly DesignRepositoryInterface $designRepository
    ) {
        parent::__construct($context);
    }

    /**
     * @inheritDoc
     */
    public function execute(): Page|Redirect
    {
        $id = (int) $this->getRequest()->getParam('id');
        $title = (string) __('New design');
        if ($id > 0) {
            try {
                $title = $this->designRepository->getById($id)->getName();
            } catch (NoSuchEntityException $e) {
                $this->messageManager->addErrorMessage(__('This design no longer exists.'));
                /** @var Redirect $redirect */
                $redirect = $this->resultRedirectFactory->create();

                return $redirect->setPath('*/*/');
            }
        }
        /** @var Page $page */
        $page = $this->pageFactory->create();
        $page->setActiveMenu('Iranimij_OpenLabel::designs');
        $page->getConfig()->getTitle()->prepend($title);

        return $page;
    }
}
