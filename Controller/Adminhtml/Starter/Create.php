<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Controller\Adminhtml\Starter;

use Iranimij\OpenLabel\Model\Indexer\IndexReader;
use Iranimij\OpenLabel\Model\Starter\Creator;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\StoreManagerInterface;

/**
 * One click on an empty-state starter: the label is created, indexed and live.
 */
class Create extends Action implements HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'Iranimij_OpenLabel::labels';

    /**
     * @param Context $context
     * @param Creator $creator
     * @param IndexReader $indexReader
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        private readonly Creator $creator,
        private readonly IndexReader $indexReader,
        private readonly StoreManagerInterface $storeManager
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
            $label = $this->creator->create((string) $this->getRequest()->getParam('starter'));
            $count = $this->indexReader->countProducts(
                (int) $label->getLabelId(),
                (int) $this->storeManager->getDefaultStoreView()?->getId()
            );
            $this->messageManager->addSuccessMessage((string) __(
                'Your "%1" label is live and matches %2 products. Open it to change the design, the conditions or where it shows.',
                $label->getName(),
                $count
            ));
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        return $redirect->setPath('openlabel/label/index');
    }
}
