<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Model\Variable;

/**
 * Result of rendering a label text: safe HTML plus the variables that came back empty.
 */
class Rendered
{
    /**
     * @param string $html
     * @param string[] $emptyVariables e.g. ["STOCK_QTY", "ATTR:size"]
     */
    public function __construct(
        private readonly string $html,
        private readonly array $emptyVariables
    ) {
    }

    /**
     * @return string allow-listed HTML, safe to print without further escaping
     */
    public function getHtml(): string
    {
        return $this->html;
    }

    /**
     * @return string[]
     */
    public function getEmptyVariables(): array
    {
        return $this->emptyVariables;
    }

    /**
     * @return bool
     */
    public function hasEmptyVariable(): bool
    {
        return $this->emptyVariables !== [];
    }
}
