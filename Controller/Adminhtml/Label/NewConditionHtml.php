<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Controller\Adminhtml\Label;

use Iranimij\OpenLabel\Block\Adminhtml\Label\Edit\Conditions;
use Iranimij\OpenLabel\Model\Rule\RuleFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Rule\Model\Condition\AbstractCondition;

/**
 * Renders one new row of the advanced rule tree ("add condition" in the form). Only OpenLabel condition classes
 * can be instantiated.
 */
class NewConditionHtml extends Action implements HttpGetActionInterface, HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'Iranimij_OpenLabel::labels';

    private const ALLOWED_NAMESPACE = 'Iranimij\\OpenLabel\\Model\\';

    /**
     * @param Context $context
     * @param RawFactory $rawFactory
     * @param RuleFactory $ruleFactory
     */
    public function __construct(
        Context $context,
        private readonly RawFactory $rawFactory,
        private readonly RuleFactory $ruleFactory
    ) {
        parent::__construct($context);
    }

    /**
     * @inheritDoc
     */
    public function execute(): Raw
    {
        $raw = $this->rawFactory->create();
        $typeParam = str_replace('-', '\\', (string) $this->getRequest()->getParam('type'));
        [$type, $attribute] = array_pad(explode('|', $typeParam, 2), 2, null);
        if (!str_starts_with((string) $type, self::ALLOWED_NAMESPACE)
            || !class_exists((string) $type)
            || !is_subclass_of((string) $type, AbstractCondition::class)
        ) {
            return $raw->setContents('');
        }
        /** @var AbstractCondition $condition */
        $condition = $this->_objectManager->create((string) $type);
        $condition->setData('id', (string) $this->getRequest()->getParam('id'));
        $condition->setData('type', $type);
        $condition->setData('rule', $this->ruleFactory->create());
        $condition->setData('prefix', 'conditions');
        if ($attribute !== null && $attribute !== '') {
            $condition->setData('attribute', $attribute);
        }
        $condition->setData('js_form_object', (string) $this->getRequest()->getParam('form', Conditions::FIELDSET_ID));
        $condition->setData(
            'form_name',
            (string) $this->getRequest()->getParam('form_namespace', Conditions::FORM_NAMESPACE)
        );

        return $raw->setContents($condition->asHtmlRecursive());
    }
}
