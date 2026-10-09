<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Model\Condition\Stock;

use Magento\Catalog\Api\Data\ProductInterface;

/**
 * Where salable quantity and salability live: the MSI stock index of the website's stock, or the legacy stock status.
 */
interface StockDataInterface
{
    /**
     * Unprefixed table (or view) name holding the stock data of the website.
     *
     * @param int $websiteId
     * @return string
     */
    public function getTableName(int $websiteId): string;

    /**
     * Join condition between the alias and the product collection's `e` table.
     *
     * @param string $alias
     * @param int $websiteId
     * @return string
     */
    public function getJoinCondition(string $alias, int $websiteId): string;

    /**
     * @return string column with the salable quantity
     */
    public function getQtyColumn(): string;

    /**
     * @return string column with 1/0 salability
     */
    public function getSalableColumn(): string;

    /**
     * @param ProductInterface $product
     * @param int $websiteId
     * @return bool
     */
    public function isSalable(ProductInterface $product, int $websiteId): bool;

    /**
     * @param ProductInterface $product
     * @param int $websiteId
     * @return float
     */
    public function getSalableQty(ProductInterface $product, int $websiteId): float;
}
