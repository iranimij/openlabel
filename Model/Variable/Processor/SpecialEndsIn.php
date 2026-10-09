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
use Magento\Framework\Stdlib\DateTime\DateTime;

/**
 * {SPECIAL_ENDS_IN}: "3 days" or "5 hours" until the special price ends (the end date is valid through that day).
 */
class SpecialEndsIn implements VariableProcessorInterface
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
        return 'SPECIAL_ENDS_IN';
    }

    /**
     * @inheritDoc
     */
    public function getValue(ProductInterface $product, Context $context, string $argument = ''): Value
    {
        $remaining = SpecialEndDate::remainingSeconds($product, $this->dateTime);
        if ($remaining === null) {
            return Value::empty();
        }
        $days = (int) floor($remaining / 86400);
        if ($days >= 1) {
            return Value::text((string) ($days === 1 ? __('1 day') : __('%1 days', $days)));
        }
        $hours = max(1, (int) ceil($remaining / 3600));

        return Value::text((string) ($hours === 1 ? __('1 hour') : __('%1 hours', $hours)));
    }
}
