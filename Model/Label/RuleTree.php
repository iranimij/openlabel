<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Model\Label;

use Iranimij\OpenLabel\Model\Rule\Rule;
use Iranimij\OpenLabel\Model\Rule\RuleFactory;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Bridge between the core rule tree UI and the stored "advanced" tree: the form posts the flat
 * rule[conditions][1--1…] format, storage keeps the recursive array.
 */
class RuleTree
{
    /**
     * @param RuleFactory $ruleFactory
     * @param Json $json
     */
    public function __construct(
        private readonly RuleFactory $ruleFactory,
        private readonly Json $json
    ) {
    }

    /**
     * @param array<string, mixed> $rulePost the "rule" part of the form POST
     * @return array<string, mixed>|null the advanced tree, null when it has no conditions
     */
    public function fromPost(array $rulePost): ?array
    {
        $rule = $this->ruleFactory->create();
        $rule->loadPost(['conditions' => $rulePost['conditions'] ?? []]);
        $tree = $rule->getConditions()->asArray();

        return ($tree['conditions'] ?? []) === [] ? null : $tree;
    }

    /**
     * A rule model holding the advanced tree, for rendering it in the form.
     *
     * @param array<string, mixed>|null $advanced
     * @return Rule
     */
    public function toRule(?array $advanced): Rule
    {
        $rule = $this->ruleFactory->create();
        if ($advanced !== null) {
            $rule->setConditionsSerialized($this->json->serialize($advanced));
        }

        return $rule;
    }
}
