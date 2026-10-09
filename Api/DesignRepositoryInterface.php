<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Api;

use Iranimij\OpenLabel\Api\Data\DesignInterface;
use Iranimij\OpenLabel\Api\Data\DesignSearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * Designs are saved with their store-scoped texts.
 *
 * @api
 */
interface DesignRepositoryInterface
{
    /**
     * @param DesignInterface $design
     * @return DesignInterface
     * @throws \Magento\Framework\Validation\ValidationException when a rule from the entity spec fails
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(DesignInterface $design): DesignInterface;

    /**
     * @param int $designId
     * @return DesignInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById(int $designId): DesignInterface;

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return DesignSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): DesignSearchResultsInterface;

    /**
     * @param DesignInterface $design
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotDeleteException when labels still use the design
     */
    public function delete(DesignInterface $design): bool;

    /**
     * @param int $designId
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function deleteById(int $designId): bool;
}
