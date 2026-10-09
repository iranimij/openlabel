<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Model\Rule\Condition;

use Magento\CatalogWidget\Model\Rule\Condition\Product as WidgetProductCondition;

/**
 * Native product attribute condition.
 *
 * @method $this setType(string $type)
 * @method array<string, string> getAttributeOption()
 * Extends the catalog widget condition because it already resolves
 * store-scoped attributes, the price index, categories and SKU lists into SQL (06 · F4).
 */
class Product extends WidgetProductCondition
{
    /**
     * @var string
     */
    protected $elementName = 'rule';

    /**
     * @inheritDoc
     */
    public function loadAttributeOptions()
    {
        parent::loadAttributeOptions();
        $this->setType(self::class);

        return $this;
    }
}
