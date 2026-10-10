<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Controller\Adminhtml\Label;

use Iranimij\OpenLabel\Api\LabelRepositoryInterface;
use Iranimij\OpenLabel\Model\Label\FormMapper;
use Iranimij\OpenLabel\Model\Label\Validator;
use Iranimij\OpenLabel\Model\LabelFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\LocalizedException;

/**
 * Inline validation for the label form (UI form validate_url): the form shows the messages above the fields
 * without a page reload; nothing is saved.
 */
class Validate extends Action implements HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'Iranimij_OpenLabel::labels';

    /**
     * @param Context $context
     * @param JsonFactory $jsonFactory
     * @param LabelRepositoryInterface $labelRepository
     * @param LabelFactory $labelFactory
     * @param FormMapper $formMapper
     * @param Validator $validator
     */
    public function __construct(
        Context $context,
        private readonly JsonFactory $jsonFactory,
        private readonly LabelRepositoryInterface $labelRepository,
        private readonly LabelFactory $labelFactory,
        private readonly FormMapper $formMapper,
        private readonly Validator $validator
    ) {
        parent::__construct($context);
    }

    /**
     * @inheritDoc
     */
    public function execute(): Json
    {
        /** @var \Magento\Framework\App\Request\Http $request */
        $request = $this->getRequest();
        /** @var array<string, mixed> $data */
        $data = (array) $request->getPostValue();
        $id = (int) ($data['label_id'] ?? 0);
        try {
            $label = $id > 0 ? $this->labelRepository->getById($id) : $this->labelFactory->create();
            $data += ['placements' => []];
            $this->formMapper->apply($label, $data);
            $messages = array_map('strval', $this->validator->validate($label));
        } catch (LocalizedException $e) {
            $messages = [$e->getMessage()];
        }

        return $this->jsonFactory->create()->setData(['error' => $messages !== [], 'messages' => $messages]);
    }
}
