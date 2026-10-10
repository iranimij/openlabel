<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Block\Adminhtml\Design\Edit;

use Iranimij\OpenLabel\Api\DesignRepositoryInterface;
use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Shared state for the design form buttons: the current design id and whether it is built-in.
 */
class GenericButton
{
    /**
     * @param Context $context
     * @param DesignRepositoryInterface $designRepository
     */
    public function __construct(
        protected readonly Context $context,
        private readonly DesignRepositoryInterface $designRepository
    ) {
    }

    /**
     * @return int
     */
    protected function getDesignId(): int
    {
        return (int) $this->context->getRequest()->getParam('id');
    }

    /**
     * @return bool
     */
    protected function isSystem(): bool
    {
        if ($this->getDesignId() === 0) {
            return false;
        }
        try {
            return $this->designRepository->getById($this->getDesignId())->isSystem();
        } catch (NoSuchEntityException $e) {
            return false;
        }
    }

    /**
     * @param string $route
     * @param array<string, mixed> $params
     * @return string
     */
    protected function getUrl(string $route, array $params = []): string
    {
        return $this->context->getUrlBuilder()->getUrl($route, $params);
    }
}
