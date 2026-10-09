<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Model\Indexer;

/**
 * Product ids that gained or lost a label after a reindex; drives cache-tag cleaning (02 · Architecture §5).
 */
class Diff
{
    /** @var int[] */
    public readonly array $added;

    /** @var int[] */
    public readonly array $removed;

    /**
     * @param int[] $before product ids before the reindex
     * @param int[] $after product ids after the reindex
     */
    public function __construct(array $before, array $after)
    {
        $before = array_values(array_unique(array_map('intval', $before)));
        $after = array_values(array_unique(array_map('intval', $after)));
        $this->added = array_values(array_diff($after, $before));
        $this->removed = array_values(array_diff($before, $after));
    }

    /**
     * @return int[] added then removed
     */
    public function changed(): array
    {
        return array_merge($this->added, $this->removed);
    }

    /**
     * @return bool
     */
    public function isChanged(): bool
    {
        return $this->added !== [] || $this->removed !== [];
    }
}
