<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Controller\Adminhtml\Label;

use Iranimij\OpenLabel\Api\Data\LabelInterface;
use Iranimij\OpenLabel\Api\LabelRepositoryInterface;
use Iranimij\OpenLabel\Model\ResourceModel\Label\CollectionFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Ui\Component\MassAction\Filter;

/**
 * Grid mass action: enable or disable the selected labels (each save reindexes the label).
 */
class MassStatus extends Action implements HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'Iranimij_OpenLabel::labels';

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param LabelRepositoryInterface $labelRepository
     */
    public function __construct(
        Context $context,
        private readonly Filter $filter,
        private readonly CollectionFactory $collectionFactory,
        private readonly LabelRepositoryInterface $labelRepository
    ) {
        parent::__construct($context);
    }

    /**
     * @inheritDoc
     */
    public function execute(): Redirect
    {
        $status = (int) $this->getRequest()->getParam('status') === LabelInterface::STATUS_ENABLED
            ? LabelInterface::STATUS_ENABLED
            : LabelInterface::STATUS_DISABLED;
        $done = 0;
        try {
            foreach ($this->filter->getCollection($this->collectionFactory->create())->getAllIds() as $id) {
                $label = $this->labelRepository->getById((int) $id);
                if ($label->getStatus() !== $status) {
                    $this->labelRepository->save($label->setStatus($status));
                }
                $done++;
            }
            $this->messageManager->addSuccessMessage(
                $status === LabelInterface::STATUS_ENABLED
                    ? __('%1 label(s) enabled.', $done)
                    : __('%1 label(s) disabled.', $done)
            );
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        /** @var Redirect $redirect */
        $redirect = $this->resultRedirectFactory->create();

        return $redirect->setPath('*/*/');
    }
}
