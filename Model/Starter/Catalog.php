<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Model\Starter;

use Iranimij\OpenLabel\Model\Design\SystemDesignCatalog;

/**
 * The three one-click starters of the empty labels grid (08 · UX Spec §3). Default positions were checked free of
 * collisions on the Hyvä 1.4 product card in M0: Sale top start, New top end, Low stock bottom start.
 */
class Catalog
{
    /**
     * @return array<string, array{name: string, title: string, hint: string, design: string, quick: array<string, string>, position: string}>
     */
    public function getStarters(): array
    {
        return [
            'sale' => [
                'name' => 'Sale -{SAVE_PERCENT}%',
                'title' => (string) __('Sale -{SAVE_PERCENT}%'),
                'hint' => (string) __('Every discounted product, with its percentage.'),
                'design' => SystemDesignCatalog::SALE,
                'quick' => ['on_sale' => '1', 'on_sale_min' => ''],
                'position' => 'tl',
            ],
            'new' => [
                'name' => 'New (30 days)',
                'title' => (string) __('New (30 days)'),
                'hint' => (string) __('Products added in the last 30 days.'),
                'design' => SystemDesignCatalog::NEW,
                'quick' => ['is_new' => '1', 'new_days' => '30'],
                'position' => 'tr',
            ],
            'low_stock' => [
                'name' => 'Only {STOCK_QTY} left',
                'title' => (string) __('Only {STOCK_QTY} left'),
                'hint' => (string) __('Products with 5 or fewer pieces left.'),
                'design' => SystemDesignCatalog::LOW_STOCK,
                'quick' => ['low_stock' => '1', 'low_stock_qty' => '5'],
                'position' => 'bl',
            ],
        ];
    }
}
