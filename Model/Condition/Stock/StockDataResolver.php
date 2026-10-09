<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Model\Condition\Stock;

use Magento\Framework\Module\Manager;
use Magento\Framework\ObjectManagerInterface;

/**
 * Picks MSI stock data when the MSI modules are enabled, the legacy stock status otherwise (06 · F6).
 */
class StockDataResolver
{
    private const MSI_MODULES = ['Magento_InventorySalesApi', 'Magento_InventoryIndexer'];

    private ?StockDataInterface $resolved = null;

    /**
     * @param Manager $moduleManager
     * @param ObjectManagerInterface $objectManager used only to instantiate the optional MSI bridge
     * @param LegacyStockData $legacyStockData
     */
    public function __construct(
        private readonly Manager $moduleManager,
        private readonly ObjectManagerInterface $objectManager,
        private readonly LegacyStockData $legacyStockData
    ) {
    }

    /**
     * @return StockDataInterface
     */
    public function get(): StockDataInterface
    {
        if ($this->resolved === null) {
            $msi = true;
            foreach (self::MSI_MODULES as $module) {
                $msi = $msi && $this->moduleManager->isEnabled($module);
            }
            $this->resolved = $msi ? $this->objectManager->get(MsiStockData::class) : $this->legacyStockData;
        }

        return $this->resolved;
    }
}
