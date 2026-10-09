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
 * {NEW_FOR}: whole days since the product was created.
 */
class NewFor implements VariableProcessorInterface
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
        return 'NEW_FOR';
    }

    /**
     * @inheritDoc
     */
    public function getValue(ProductInterface $product, Context $context, string $argument = ''): Value
    {
        $created = $product->getCreatedAt();
        if ($created === null || $created === '') {
            return Value::empty();
        }
        $seconds = $this->dateTime->gmtTimestamp() - $this->dateTime->timestamp((string) $created);

        return Value::number((int) floor($seconds / 86400));
    }
}
