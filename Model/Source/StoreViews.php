<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Model\Source;

use Magento\Store\Ui\Component\Listing\Column\Store\Options;

/**
 * Store views grouped by website, with "All Store Views" (0 = no restriction) first.
 */
class StoreViews extends Options
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function toOptionArray()
    {
        $options = parent::toOptionArray();
        array_unshift($options, ['label' => __('All Store Views'), 'value' => '0']);

        return $options;
    }
}
