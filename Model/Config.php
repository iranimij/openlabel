<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Model;

use Iranimij\Base\Model\Config\TypedReader;
use Magento\Store\Model\ScopeInterface;

/**
 * OpenLabel settings under Stores › Configuration › Iranimij › OpenLabel.
 */
class Config
{
    public const XML_ENABLED = 'openlabel/general/enabled';
    public const XML_DEFAULT_MAX_LABELS = 'openlabel/general/default_max_labels';
    public const XML_DEBUG = 'openlabel/general/debug';

    /**
     * @param TypedReader $reader
     */
    public function __construct(
        private readonly TypedReader $reader
    ) {
    }

    /**
     * Max labels per position for new placements, 1..10.
     *
     * @return int
     */
    public function getDefaultMaxLabels(): int
    {
        $value = $this->reader->getInt(self::XML_DEFAULT_MAX_LABELS, 'default');

        return $value < 1 ? 3 : min($value, 10);
    }

    /**
     * Debug mode adds data-ol-label attributes to the shop's label markup (used by the render package).
     *
     * @param int|string|null $storeId
     * @return bool
     */
    public function isDebug(int|string|null $storeId = null): bool
    {
        return $this->reader->getBool(self::XML_DEBUG, ScopeInterface::SCOPE_STORE, $storeId);
    }
}
