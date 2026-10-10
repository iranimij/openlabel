<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Controller\Adminhtml\Label;

use Iranimij\OpenLabel\Api\LabelRepositoryInterface;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Page;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Result\PageFactory;

/**
 * Label form (new or existing).
 */
class Edit extends Action implements HttpGetActionInterface
{
    public const ADMIN_RESOURCE = 'Iranimij_OpenLabel::labels';

    /**
     * @param Context $context
     * @param PageFactory $pageFactory
     * @param LabelRepositoryInterface $labelRepository
     */
    public function __construct(
        Context $context,
        private readonly PageFactory $pageFactory,
        private readonly LabelRepositoryInterface $labelRepository
    ) {
        parent::__construct($context);
    }

    /**
     * @inheritDoc
     */
    public function execute(): Page|Redirect
    {
        $id = (int) $this->getRequest()->getParam('id');
        $title = (string) __('New label');
        if ($id > 0) {
            try {
                $title = $this->labelRepository->getById($id)->getName();
            } catch (NoSuchEntityException $e) {
                $this->messageManager->addErrorMessage((string) __('This label no longer exists.'));
                /** @var Redirect $redirect */
                $redirect = $this->resultRedirectFactory->create();

                return $redirect->setPath('*/*/');
            }
        }
        /** @var Page $page */
        $page = $this->pageFactory->create();
        $page->setActiveMenu('Iranimij_OpenLabel::labels');
        $page->getConfig()->getTitle()->prepend($title);

        return $page;
    }
}
