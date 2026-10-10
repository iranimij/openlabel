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
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Deletes a label (its index rows and cached pages are cleaned by the repository plugin).
 */
class Delete extends Action implements HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'Iranimij_OpenLabel::labels';

    /**
     * @param Context $context
     * @param LabelRepositoryInterface $labelRepository
     */
    public function __construct(
        Context $context,
        private readonly LabelRepositoryInterface $labelRepository
    ) {
        parent::__construct($context);
    }

    /**
     * @inheritDoc
     */
    public function execute(): Redirect
    {
        /** @var Redirect $redirect */
        $redirect = $this->resultRedirectFactory->create();
        $id = (int) $this->getRequest()->getParam('id');
        try {
            $this->labelRepository->deleteById($id);
            $this->messageManager->addSuccessMessage((string) __('The label was deleted.'));
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());

            return $redirect->setPath('*/*/edit', ['id' => $id]);
        }

        return $redirect->setPath('*/*/');
    }
}
