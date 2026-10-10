<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Model\Condition;

use Magento\Catalog\Model\Product;

/**
 * Final or regular price from the price index of the rule's website and customer group.
 */
class PriceRange extends AbstractBuiltIn
{
    use PriceIndexJoin;

    public const FINAL_PRICE = 'final_price';
    public const PRICE = 'price';

    /**
     * @inheritDoc
     */
    protected function attributeOptions(): array
    {
        return [
            self::FINAL_PRICE => __('Final price (after discounts)'),
            self::PRICE => __('Regular price'),
        ];
    }

    /**
     * @inheritDoc
     */
    public function getMappedSqlField()
    {
        return self::PRICE_ALIAS . '.' . ((string) $this->getAttribute() === self::PRICE ? 'price' : 'final_price');
    }

    /**
     * @inheritDoc
     */
    protected function productValue(Product $product): mixed
    {
        [$price, $final] = $this->prices($product);

        return (string) $this->getAttribute() === self::PRICE ? $price : $final;
    }
}
