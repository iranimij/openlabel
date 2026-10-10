<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Controller\Adminhtml\Label;

use Iranimij\OpenLabel\Api\LabelRepositoryInterface;
use Iranimij\OpenLabel\Model\Indexer\IndexReader;
use Iranimij\OpenLabel\Model\Indexer\LabelReindexer;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\StoreManagerInterface;

/**
 * "Reindex now" in the matched products section: rebuilds one label's index rows (and cleans its cached pages).
 */
class Reindex extends Action implements HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'Iranimij_OpenLabel::labels';

    /**
     * @param Context $context
     * @param LabelRepositoryInterface $labelRepository
     * @param LabelReindexer $labelReindexer
     * @param IndexReader $indexReader
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        private readonly LabelRepositoryInterface $labelRepository,
        private readonly LabelReindexer $labelReindexer,
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
        $id = (int) $this->getRequest()->getParam('id');
        if ($id === 0) {
            return $redirect->setPath('*/*/');
        }
        try {
            $this->labelReindexer->reindex($this->labelRepository->getById($id));
            $count = $this->indexReader->countProducts($id, (int) $this->storeManager->getDefaultStoreView()?->getId());
            $this->messageManager->addSuccessMessage(
                $count === 1
                    ? (string) __('Reindexed. 1 product matches in the default store view.')
                    : (string) __('Reindexed. %1 products match in the default store view.', $count)
            );
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        return $redirect->setPath('*/*/edit', ['id' => $id]);
    }
}
