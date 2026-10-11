<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Plugin;

use Iranimij\OpenLabel\Api\Data\DesignInterface;
use Iranimij\OpenLabel\Api\DesignRepositoryInterface;
use Iranimij\OpenLabel\Model\Cache\Invalidator;

/**
 * A saved design cleans the pages and cached product cards that show it, also when only a store text changed
 * (the model then has no data changes and the core model cache cleaning does not run).
 */
class DesignCachePlugin
{
    /**
     * @param Invalidator $invalidator
     */
    public function __construct(private readonly Invalidator $invalidator)
    {
    }

    /**
     * @param DesignRepositoryInterface $subject
     * @param DesignInterface $result
     * @return DesignInterface
     */
    public function afterSave(DesignRepositoryInterface $subject, DesignInterface $result): DesignInterface
    {
        if ($result->getDesignId() !== null) {
            $this->invalidator->cleanDesign((int) $result->getDesignId());
        }

        return $result;
    }
}
