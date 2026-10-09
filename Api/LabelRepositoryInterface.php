<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Api;

use Iranimij\OpenLabel\Api\Data\LabelInterface;
use Iranimij\OpenLabel\Api\Data\LabelSearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * Labels are saved with their placements; placements have no repository of their own in 1.0.
 *
 * @api
 */
interface LabelRepositoryInterface
{
    /**
     * Validate and save a label with its placements. The index rows are rebuilt by the indexer afterwards.
     *
     * @param LabelInterface $label
     * @return LabelInterface
     * @throws \Magento\Framework\Validation\ValidationException when a rule from the entity spec fails
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(LabelInterface $label): LabelInterface;

    /**
     * @param int $labelId
     * @return LabelInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById(int $labelId): LabelInterface;

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return LabelSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): LabelSearchResultsInterface;

    /**
     * @param LabelInterface $label
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(LabelInterface $label): bool;

    /**
     * @param int $labelId
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function deleteById(int $labelId): bool;
}
