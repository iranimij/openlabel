<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Controller\Adminhtml\Design;

use Iranimij\OpenLabel\Api\DesignRepositoryInterface;
use Iranimij\OpenLabel\Model\Design\Duplicator;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * "Duplicate to edit": copies a design and opens the copy. GET is accepted for the form button (admin URLs carry
 * the secret key); nothing is changed on the source design.
 */
class Duplicate extends Action implements HttpGetActionInterface, HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'Iranimij_OpenLabel::designs';

    /**
     * @param Context $context
     * @param DesignRepositoryInterface $designRepository
     * @param Duplicator $duplicator
     */
    public function __construct(
        Context $context,
        private readonly DesignRepositoryInterface $designRepository,
        private readonly Duplicator $duplicator
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
        try {
            $copy = $this->duplicator->duplicate(
                $this->designRepository->getById((int) $this->getRequest()->getParam('id'))
            );
            $this->messageManager->addSuccessMessage(__('Your copy is ready to edit.'));

            return $redirect->setPath('*/*/edit', ['id' => $copy->getDesignId()]);
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        return $redirect->setPath('*/*/');
    }
}
