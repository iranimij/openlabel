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
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;

/**
 * {ATTR:code}: a product attribute's display value (option labels for select and multiselect attributes).
 */
class Attr implements VariableProcessorInterface
{
    /**
     * @inheritDoc
     */
    public function getCode(): string
    {
        return 'ATTR';
    }

    /**
     * @inheritDoc
     */
    public function getValue(ProductInterface $product, Context $context, string $argument = ''): Value
    {
        if ($argument === '' || !$product instanceof Product || !$product->hasData($argument)) {
            return Value::empty();
        }
        $value = $product->getData($argument);
        if ($value !== null && $value !== '' && $this->isOptionAttribute($product, $argument)) {
            $text = $product->getAttributeText($argument);
            $value = $text === false ? $value : $text;
        }
        if (is_array($value)) {
            $value = implode(', ', array_map('strval', $value));
        }

        return Value::text($value === null ? null : (string) $value);
    }

    /**
     * @param Product $product
     * @param string $code
     * @return bool
     */
    private function isOptionAttribute(Product $product, string $code): bool
    {
        $resource = $product->getResource();
        if (!$resource instanceof \Magento\Catalog\Model\ResourceModel\Product) {
            return false;
        }
        $attribute = $resource->getAttribute($code);

        return $attribute instanceof AbstractAttribute
            && in_array($attribute->getFrontendInput(), ['select', 'multiselect'], true);
    }
}
