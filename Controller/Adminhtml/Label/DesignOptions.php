<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Controller\Adminhtml\Label;

use Iranimij\OpenLabel\Model\Design\PickerOptions;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;

/**
 * Fresh list for the design picker after the merchant created a design in another tab.
 */
class DesignOptions extends Action implements HttpGetActionInterface
{
    public const ADMIN_RESOURCE = 'Iranimij_OpenLabel::labels';

    /**
     * @param Context $context
     * @param JsonFactory $jsonFactory
     * @param PickerOptions $pickerOptions
     */
    public function __construct(
        Context $context,
        private readonly JsonFactory $jsonFactory,
        private readonly PickerOptions $pickerOptions
    ) {
        parent::__construct($context);
    }

    /**
     * @inheritDoc
     */
    public function execute(): Json
    {
        return $this->jsonFactory->create()->setData(['designs' => $this->pickerOptions->get()]);
    }
}
