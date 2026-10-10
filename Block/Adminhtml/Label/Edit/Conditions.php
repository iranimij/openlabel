<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Block\Adminhtml\Label\Edit;

use Iranimij\OpenLabel\Api\LabelRepositoryInterface;
use Iranimij\OpenLabel\Model\Label\QuickConditions;
use Iranimij\OpenLabel\Model\Label\RuleTree;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Form\Renderer\Fieldset;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Rule\Block\Conditions as ConditionsRenderer;
use Magento\Rule\Model\Condition\AbstractCondition;

/**
 * "Advanced conditions": the core rule tree inside the label form. Its inputs carry data-form-part so the UI form
 * posts them with the other fields.
 */
class Conditions extends Generic
{
    public const FORM_NAMESPACE = 'openlabel_label_form';
    public const FIELDSET_ID = 'rule_conditions_fieldset';

    /**
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param Fieldset $fieldsetRenderer
     * @param ConditionsRenderer $conditionsRenderer
     * @param LabelRepositoryInterface $labelRepository
     * @param QuickConditions $quickConditions
     * @param RuleTree $ruleTree
     * @param array<string, mixed> $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        private readonly Fieldset $fieldsetRenderer,
        private readonly ConditionsRenderer $conditionsRenderer,
        private readonly LabelRepositoryInterface $labelRepository,
        private readonly QuickConditions $quickConditions,
        private readonly RuleTree $ruleTree,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @inheritDoc
     */
    protected function _prepareForm()
    {
        $form = $this->_formFactory->create();
        $form->setData('html_id_prefix', 'rule_');
        $this->fieldsetRenderer->setTemplate('Iranimij_OpenLabel::label/conditions.phtml')
            ->setData('new_child_url', $this->getUrl('openlabel/label/newConditionHtml', [
                'form' => self::FIELDSET_ID,
                'form_namespace' => self::FORM_NAMESPACE,
            ]));
        $fieldset = $form->addFieldset('conditions_fieldset', ['legend' => __('Advanced conditions')])
            ->setRenderer($this->fieldsetRenderer);

        $rule = $this->ruleTree->toRule($this->advancedTree());
        $conditions = $rule->getConditions();
        $this->prepareCondition($conditions);
        $fieldset->addField('conditions', 'text', [
            'name' => 'conditions',
            'label' => __('Conditions'),
            'title' => __('Conditions'),
            'required' => false,
            'data-form-part' => self::FORM_NAMESPACE,
        ])->setData('rule', $rule)->setRenderer($this->conditionsRenderer);

        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * @return array<string, mixed>|null
     */
    private function advancedTree(): ?array
    {
        $id = (int) $this->getRequest()->getParam('id');
        if ($id === 0) {
            return null;
        }
        try {
            return $this->quickConditions->decompose($this->labelRepository->getById($id)->getConditionsSerialized())[1];
        } catch (NoSuchEntityException $e) {
            return null;
        }
    }

    /**
     * @param AbstractCondition $condition
     * @return void
     */
    private function prepareCondition(AbstractCondition $condition): void
    {
        $condition->setData('form_name', self::FORM_NAMESPACE);
        $condition->setData('js_form_object', self::FIELDSET_ID);
        foreach ((array) $condition->getData('conditions') as $child) {
            if ($child instanceof AbstractCondition) {
                $this->prepareCondition($child);
            }
        }
    }
}
