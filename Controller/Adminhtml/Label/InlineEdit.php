<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Controller\Adminhtml\Label;

use Iranimij\OpenLabel\Api\LabelRepositoryInterface;
use Iranimij\OpenLabel\Model\Label\DateConverter;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Validation\ValidationException;

/**
 * Grid inline edit of name, status, priority and the schedule. Dates arrive in the shop timezone and are stored
 * in UTC; a date-only end means the end of that day.
 */
class InlineEdit extends Action implements HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'Iranimij_OpenLabel::labels';

    /**
     * @param Context $context
     * @param JsonFactory $jsonFactory
     * @param LabelRepositoryInterface $labelRepository
     * @param DateConverter $dateConverter
     */
    public function __construct(
        Context $context,
        private readonly JsonFactory $jsonFactory,
        private readonly LabelRepositoryInterface $labelRepository,
        private readonly DateConverter $dateConverter
    ) {
        parent::__construct($context);
    }

    /**
     * @inheritDoc
     */
    public function execute(): Json
    {
        $messages = [];
        $items = $this->getRequest()->getParam('items', []);
        if (!$this->getRequest()->getParam('isAjax') || !is_array($items) || $items === []) {
            $messages[] = (string) __('Please correct the data sent.');
        } else {
            foreach ($items as $id => $values) {
                $error = $this->saveRow((int) $id, is_array($values) ? $values : []);
                if ($error !== null) {
                    $messages[] = $error;
                }
            }
        }

        return $this->jsonFactory->create()->setData(['messages' => $messages, 'error' => $messages !== []]);
    }

    /**
     * @param int $id
     * @param array<string, mixed> $values
     * @return string|null error message
     */
    private function saveRow(int $id, array $values): ?string
    {
        try {
            $label = $this->labelRepository->getById($id);
            if (array_key_exists('name', $values)) {
                $label->setName(trim((string) $values['name']));
            }
            if (array_key_exists('status', $values)) {
                $label->setStatus((int) $values['status'] === 1 ? 1 : 0);
            }
            if (array_key_exists('priority', $values)) {
                $label->setPriority(max(0, (int) $values['priority']));
            }
            if (array_key_exists('valid_from', $values)) {
                $label->setValidFrom($this->scheduleDate($label->getValidFrom(), (string) $values['valid_from'], false));
            }
            if (array_key_exists('valid_to', $values)) {
                $label->setValidTo($this->scheduleDate($label->getValidTo(), (string) $values['valid_to'], true));
            }
            $this->labelRepository->save($label);
        } catch (ValidationException $e) {
            $errors = array_map(
                static fn (LocalizedException $error): string => $error->getMessage(),
                $e->getErrors()
            ) ?: [$e->getMessage()];

            return (string) __('[Label ID: %1] %2', $id, implode(' ', $errors));
        } catch (LocalizedException $e) {
            return (string) __('[Label ID: %1] %2', $id, $e->getMessage());
        }

        return null;
    }

    /**
     * The grid's date editor posts the day only, for every row edit. Keep the stored time while the day is unchanged.
     *
     * @param string|null $current UTC
     * @param string $submitted
     * @param bool $endOfDay
     * @return string|null UTC
     * @throws LocalizedException
     */
    private function scheduleDate(?string $current, string $submitted, bool $endOfDay): ?string
    {
        $utc = $this->dateConverter->toUtc($submitted, $endOfDay);
        if ($current !== null && $utc !== null
            && substr((string) $this->dateConverter->toLocal($current), 0, 10)
                === substr((string) $this->dateConverter->toLocal($utc), 0, 10)
        ) {
            return $current;
        }

        return $utc;
    }
}
