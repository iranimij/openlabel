<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Plugin;

use Iranimij\OpenLabel\Model\Css\Regenerator;
use Psr\Log\LoggerInterface;

/**
 * Design and label saves change colours, sizes or placement offsets, so the stylesheet is rebuilt after each one.
 * A failing write (read-only media, full disk) is logged and never blocks the save.
 */
class RegenerateCssPlugin
{
    /**
     * @param Regenerator $regenerator
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly Regenerator $regenerator,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @param object $subject
     * @param mixed $result
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSave(object $subject, mixed $result): mixed
    {
        $this->regenerate();

        return $result;
    }

    /**
     * @param object $subject
     * @param mixed $result
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterDelete(object $subject, mixed $result): mixed
    {
        $this->regenerate();

        return $result;
    }

    /**
     * @param object $subject
     * @param mixed $result
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterDeleteById(object $subject, mixed $result): mixed
    {
        $this->regenerate();

        return $result;
    }

    /**
     * @return void
     */
    private function regenerate(): void
    {
        try {
            $this->regenerator->regenerateAll();
        } catch (\Throwable $e) {
            $this->logger->error('OpenLabel: stylesheet regeneration failed: ' . $e->getMessage());
        }
    }
}
