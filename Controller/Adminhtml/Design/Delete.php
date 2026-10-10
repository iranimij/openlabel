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
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Deletes a design. Built-in designs and designs still used by labels are kept.
 */
class Delete extends Action implements HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'Iranimij_OpenLabel::designs';

    /**
     * @param Context $context
     * @param DesignRepositoryInterface $designRepository
     */
    public function __construct(
        Context $context,
        private readonly DesignRepositoryInterface $designRepository
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
            $design = $this->designRepository->getById($id);
            if ($design->isSystem()) {
                $this->messageManager->addErrorMessage(
                    __('Built-in designs cannot be deleted. Duplicate one to make your own version.')
                );

                return $redirect->setPath('*/*/edit', ['id' => $id]);
            }
            $this->designRepository->delete($design);
            $this->messageManager->addSuccessMessage(__('The design was deleted.'));
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());

            return $redirect->setPath('*/*/edit', ['id' => $id]);
        }

        return $redirect->setPath('*/*/');
    }
}
