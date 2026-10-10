<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Model\Condition;

use Magento\Catalog\Model\Product;

/**
 * On sale: special price or catalog price rule, read from the price index of the rule's website and group (06 · F5).
 * Discount percent is rounded half up before comparing, like the {SAVE_PERCENT} badge shows it.
 */
class OnSale extends AbstractBuiltIn
{
    use PriceIndexJoin;

    public const ON_SALE = 'on_sale';
    public const DISCOUNT_PERCENT = 'discount_percent';
    public const DISCOUNT_AMOUNT = 'discount_amount';

    /**
     * @inheritDoc
     */
    protected function attributeOptions(): array
    {
        return [
            self::ON_SALE => __('On sale'),
            self::DISCOUNT_PERCENT => __('Discount (%)'),
            self::DISCOUNT_AMOUNT => __('Discount (amount)'),
        ];
    }

    /**
     * @inheritDoc
     */
    protected function booleanAttributes(): array
    {
        return [self::ON_SALE];
    }

    /**
     * The core Sql Builder accepts expressions only as Zend_Db_Expr instances, hence the widened return type.
     *
     * @return \Zend_Db_Expr
     * @phpstan-ignore method.childReturnType, method.childReturnType (AbstractCondition and ConditionInterface both declare string)
     */
    public function getMappedSqlField()
    {
        $p = self::PRICE_ALIAS;

        return match ((string) $this->getAttribute()) {
            self::DISCOUNT_PERCENT => new \Zend_Db_Expr(
                "ROUND(($p.price - $p.final_price) / NULLIF($p.price, 0) * 100)"
            ),
            self::DISCOUNT_AMOUNT => new \Zend_Db_Expr("($p.price - $p.final_price)"),
            default => new \Zend_Db_Expr("IF($p.final_price < $p.price, 1, 0)"),
        };
    }

    /**
     * @inheritDoc
     */
    protected function productValue(Product $product): mixed
    {
        [$price, $final] = $this->prices($product);
        if ($price <= 0.0) {
            return match ((string) $this->getAttribute()) {
                self::ON_SALE => 0,
                default => null,
            };
        }

        return match ((string) $this->getAttribute()) {
            self::DISCOUNT_PERCENT => (int) round(($price - $final) / $price * 100),
            self::DISCOUNT_AMOUNT => $price - $final,
            default => $final < $price ? 1 : 0,
        };
    }
}
