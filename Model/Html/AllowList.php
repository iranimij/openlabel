<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Model\Html;

use Magento\Framework\Escaper;

/**
 * Label text may carry a small set of inline tags (02 · Architecture §8); everything else is escaped or dropped,
 * attributes other than class/id/title/style are removed.
 */
class AllowList
{
    public const TAGS = ['b', 'strong', 'i', 'em', 'br', 'span', 'small', 'sup', 'sub'];

    /**
     * @param Escaper $escaper
     */
    public function __construct(private readonly Escaper $escaper)
    {
    }

    /**
     * @param string $html
     * @return string
     */
    public function clean(string $html): string
    {
        if ($html === '') {
            return '';
        }

        return (string) $this->escaper->escapeHtml($html, self::TAGS);
    }
}
