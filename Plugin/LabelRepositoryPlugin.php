<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Plugin;

use Iranimij\OpenLabel\Api\Data\LabelInterface;
use Iranimij\OpenLabel\Api\LabelRepositoryInterface;
use Iranimij\OpenLabel\Model\Indexer\LabelReindexer;

/**
 * A saved label is reindexed at once (whatever the indexer mode) and a deleted label leaves no rows behind.
 */
class LabelRepositoryPlugin
{
    /**
     * @param LabelReindexer $labelReindexer
     */
    public function __construct(private readonly LabelReindexer $labelReindexer)
    {
    }

    /**
     * @param LabelRepositoryInterface $subject
     * @param LabelInterface $result
     * @return LabelInterface
     */
    public function afterSave(LabelRepositoryInterface $subject, LabelInterface $result): LabelInterface
    {
        $this->labelReindexer->reindex($result);

        return $result;
    }

    /**
     * @param LabelRepositoryInterface $subject
     * @param bool $result
     * @param LabelInterface $label
     * @return bool
     */
    public function afterDelete(LabelRepositoryInterface $subject, bool $result, LabelInterface $label): bool
    {
        if ($label->getLabelId() !== null) {
            $this->labelReindexer->removeLabel((int) $label->getLabelId());
        }

        return $result;
    }
}
