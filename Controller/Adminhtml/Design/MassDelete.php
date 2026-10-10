<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Controller\Adminhtml\Design;

use Iranimij\OpenLabel\Api\DesignRepositoryInterface;
use Iranimij\OpenLabel\Model\ResourceModel\Design\CollectionFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Ui\Component\MassAction\Filter;

/**
 * Grid mass action: delete the selected designs. Built-in designs and designs still used by labels are kept.
 */
class MassDelete extends Action implements HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'Iranimij_OpenLabel::designs';

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param DesignRepositoryInterface $designRepository
     */
    public function __construct(
        Context $context,
        private readonly Filter $filter,
        private readonly CollectionFactory $collectionFactory,
        private readonly DesignRepositoryInterface $designRepository
    ) {
        parent::__construct($context);
    }

    /**
     * @inheritDoc
     */
    public function execute(): Redirect
    {
        $done = 0;
        $skippedSystem = false;
        try {
            foreach ($this->filter->getCollection($this->collectionFactory->create())->getAllIds() as $id) {
                $design = $this->designRepository->getById((int) $id);
                if ($design->isSystem()) {
                    $skippedSystem = true;
                    continue;
                }
                try {
                    $this->designRepository->delete($design);
                    $done++;
                } catch (LocalizedException $e) {
                    $this->messageManager->addErrorMessage($e->getMessage());
                }
            }
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        if ($skippedSystem) {
            $this->messageManager->addErrorMessage(
                __('Built-in designs cannot be deleted. Duplicate one to make your own version.')
            );
        }
        if ($done > 0) {
            $this->messageManager->addSuccessMessage(__('%1 design(s) deleted.', $done));
        }

        /** @var Redirect $redirect */
        $redirect = $this->resultRedirectFactory->create();

        return $redirect->setPath('*/*/');
    }
}
