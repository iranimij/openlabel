<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Controller\Adminhtml\Css;

use Iranimij\OpenLabel\Model\Css\Regenerator;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Action\HttpPostActionInterface;

/**
 * "Regenerate CSS" in the OpenLabel settings: same as `bin/magento openlabel:css:regenerate`.
 */
class Regenerate extends Action implements HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'Iranimij_OpenLabel::config';

    /**
     * @param Context $context
     * @param Regenerator $regenerator
     */
    public function __construct(
        Context $context,
        private readonly Regenerator $regenerator
    ) {
        parent::__construct($context);
    }

    /**
     * @inheritDoc
     */
    public function execute(): Redirect
    {
        try {
            $count = count($this->regenerator->regenerateAll());
            $this->messageManager->addSuccessMessage((string) __(
                'The OpenLabel stylesheet was rebuilt for %1 store view(s).',
                $count
            ));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage((string) __('The stylesheet could not be written: %1', $e->getMessage()));
        }

        /** @var Redirect $redirect */
        $redirect = $this->resultRedirectFactory->create();

        return $redirect->setPath('adminhtml/system_config/edit', ['section' => 'openlabel']);
    }
}
