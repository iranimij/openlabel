<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Model\Variable;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;

/**
 * Regular, final and special price of a loaded product, from the collection data when present
 * (listing) or from the price info otherwise (product page, composites), without extra queries.
 */
class ProductPrices
{
    private const COMPOSITE_TYPES = ['configurable', 'grouped', 'bundle'];

    /**
     * @param ProductInterface $product
     * @return float
     */
    public function regular(ProductInterface $product): float
    {
        if (!$product instanceof Product) {
            return (float) $product->getPrice();
        }
        if (in_array((string) $product->getTypeId(), self::COMPOSITE_TYPES, true)) {
            return (float) $product->getPriceInfo()->getPrice('regular_price')->getAmount()->getValue();
        }
        $price = $product->getData('price');

        return $price === null ? (float) $product->getPrice() : (float) $price;
    }

    /**
     * @param ProductInterface $product
     * @return float
     */
    public function final(ProductInterface $product): float
    {
        if (!$product instanceof Product) {
            return (float) $product->getPrice();
        }
        $final = $product->getData('final_price');
        if ($final !== null && $final !== '') {
            return (float) $final;
        }

        return (float) $product->getPriceInfo()->getPrice('final_price')->getAmount()->getValue();
    }

    /**
     * @param ProductInterface $product
     * @return float|null
     */
    public function special(ProductInterface $product): ?float
    {
        if (!$product instanceof Product) {
            return null;
        }
        $special = $product->getData('special_price');

        return $special === null || $special === '' ? null : (float) $special;
    }
}
