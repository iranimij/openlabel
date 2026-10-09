<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Cron;

use Iranimij\OpenLabel\Model\Indexer\LabelReindexer;
use Iranimij\OpenLabel\Model\ResourceModel\Label\CollectionFactory as LabelCollectionFactory;
use Iranimij\OpenLabel\Model\Rule\RuleFactory;

/**
 * Labels whose conditions depend on the date ("is new" windows, days since created) are rebuilt once a day (06 · F21).
 */
class DailyIsNew
{
    /**
     * @param LabelCollectionFactory $labelCollectionFactory
     * @param RuleFactory $ruleFactory
     * @param LabelReindexer $labelReindexer
     */
    public function __construct(
        private readonly LabelCollectionFactory $labelCollectionFactory,
        private readonly RuleFactory $ruleFactory,
        private readonly LabelReindexer $labelReindexer
    ) {
    }

    /**
     * @return void
     */
    public function execute(): void
    {
        foreach ($this->labelCollectionFactory->create()->getItems() as $label) {
            $rule = $this->ruleFactory->create();
            $rule->setConditionsSerialized((string) $label->getConditionsSerialized());
            if ($rule->isDateRelative()) {
                $this->labelReindexer->reindex($label);
            }
        }
    }
}
