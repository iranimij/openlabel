<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Controller\Adminhtml\Label;

use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result\Forward;
use Magento\Framework\App\Action\HttpGetActionInterface;

/**
 * New label: the edit form without an id.
 */
class NewAction extends Action implements HttpGetActionInterface
{
    public const ADMIN_RESOURCE = 'Iranimij_OpenLabel::labels';

    /**
     * @inheritDoc
     */
    public function execute(): Forward
    {
        /** @var Forward $forward */
        $forward = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_FORWARD);

        return $forward->forward('edit');
    }
}
