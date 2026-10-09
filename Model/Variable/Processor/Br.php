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

/**
 * {BR}: a line break.
 */
class Br implements VariableProcessorInterface
{
    /**
     * @inheritDoc
     */
    public function getCode(): string
    {
        return 'BR';
    }

    /**
     * @inheritDoc
     */
    public function getValue(ProductInterface $product, Context $context, string $argument = ''): Value
    {
        return Value::html('<br>');
    }
}
