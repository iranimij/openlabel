<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Model\Variable\Processor;

use Iranimij\OpenLabel\Api\VariableProcessorInterface;
use Iranimij\OpenLabel\Model\Variable\Context;
use Iranimij\OpenLabel\Model\Variable\Value;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Stdlib\DateTime\DateTime;

/**
 * {SPECIAL_END_DATE}: the last day of the special price, formatted for the locale; empty once it has passed.
 */
class SpecialEndDate implements VariableProcessorInterface
{
    /**
     * @param DateTime $dateTime
     */
    public function __construct(private readonly DateTime $dateTime)
    {
    }

    /**
     * @inheritDoc
     */
    public function getCode(): string
    {
        return 'SPECIAL_END_DATE';
    }

    /**
     * @inheritDoc
     */
    public function getValue(ProductInterface $product, Context $context, string $argument = ''): Value
    {
        if (self::remainingSeconds($product, $this->dateTime) === null) {
            return Value::empty();
        }

        return Value::date($product instanceof Product ? (string) $product->getData('special_to_date') : null);
    }

    /**
     * Seconds until the end of the special price's last day, null without an end date or when it has passed.
     *
     * @param ProductInterface $product
     * @param DateTime $dateTime
     * @return int|null
     */
    public static function remainingSeconds(ProductInterface $product, DateTime $dateTime): ?int
    {
        $to = $product instanceof Product ? $product->getData('special_to_date') : null;
        if ($to === null || $to === '') {
            return null;
        }
        $end = $dateTime->timestamp((string) $to) + 86400;
        $remaining = $end - $dateTime->gmtTimestamp();

        return $remaining > 0 ? $remaining : null;
    }
}
