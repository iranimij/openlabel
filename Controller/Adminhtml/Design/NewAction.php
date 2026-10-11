<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Controller\Adminhtml\Design;

use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result\Forward;
use Magento\Framework\App\Action\HttpGetActionInterface;

/**
 * New design: the edit form without an id.
 */
class NewAction extends Action implements HttpGetActionInterface
{
    public const ADMIN_RESOURCE = 'Iranimij_OpenLabel::designs';

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
