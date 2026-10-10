<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Model\Starter;

use Iranimij\OpenLabel\Api\Data\LabelInterface;
use Iranimij\OpenLabel\Api\Data\PlacementInterface;
use Iranimij\OpenLabel\Api\Data\PlacementInterfaceFactory;
use Iranimij\OpenLabel\Api\LabelRepositoryInterface;
use Iranimij\OpenLabel\Model\Design\SystemDesignCatalog;
use Iranimij\OpenLabel\Model\Label\FormMapper;
use Iranimij\OpenLabel\Model\Label\QuickConditions;
use Iranimij\OpenLabel\Model\LabelFactory;
use Iranimij\OpenLabel\Model\ResourceModel\Design\CollectionFactory as DesignCollectionFactory;
use Iranimij\OpenLabel\Model\ResourceModel\Label\CollectionFactory as LabelCollectionFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Creates a working label from a starter in one step: built-in design, quick condition, listing and product page
 * placements, enabled. Submitting the same starter again within a minute returns the label just created.
 */
class Creator
{
    private const DOUBLE_SUBMIT_SECONDS = 60;

    /**
     * @param Catalog $catalog
     * @param SystemDesignCatalog $designCatalog
     * @param LabelFactory $labelFactory
     * @param PlacementInterfaceFactory $placementFactory
     * @param LabelRepositoryInterface $labelRepository
     * @param QuickConditions $quickConditions
     * @param DesignCollectionFactory $designCollectionFactory
     * @param LabelCollectionFactory $labelCollectionFactory
     */
    public function __construct(
        private readonly Catalog $catalog,
        private readonly SystemDesignCatalog $designCatalog,
        private readonly LabelFactory $labelFactory,
        private readonly PlacementInterfaceFactory $placementFactory,
        private readonly LabelRepositoryInterface $labelRepository,
        private readonly QuickConditions $quickConditions,
        private readonly DesignCollectionFactory $designCollectionFactory,
        private readonly LabelCollectionFactory $labelCollectionFactory
    ) {
    }

    /**
     * @param string $key
     * @return LabelInterface
     * @throws LocalizedException
     */
    public function create(string $key): LabelInterface
    {
        $starter = $this->catalog->getStarters()[$key] ?? null;
        if ($starter === null) {
            throw new NoSuchEntityException(__('This starter does not exist.'));
        }
        $recent = $this->recentLabel($starter['name']);
        if ($recent !== null) {
            return $recent;
        }

        $label = $this->labelFactory->create();
        $label->setName($starter['name'])
            ->setStatus(LabelInterface::STATUS_ENABLED)
            ->setPriority(0)
            ->setDesignId($this->designId($starter['design']))
            ->setApplyToParent(true)
            ->setHideLowerPriority(false)
            ->setStoreIds([])
            ->setCustomerGroupIds([])
            ->setConditionsSerialized($this->quickConditions->compose($starter['quick'], null));
        $placements = [];
        foreach ([PlacementInterface::AREA_LISTING, PlacementInterface::AREA_PRODUCT] as $area) {
            $placements[] = $this->placementFactory->create()
                ->setArea($area)
                ->setPosition($starter['position'])
                ->setMaxLabels(FormMapper::DEFAULT_MAX_LABELS)
                ->setStacking(PlacementInterface::STACKING_VERTICAL)
                ->setGap(FormMapper::DEFAULT_GAP);
        }
        $label->setPlacements($placements);

        return $this->labelRepository->save($label);
    }

    /**
     * @param string $designKey
     * @return int
     * @throws LocalizedException
     */
    private function designId(string $designKey): int
    {
        $name = (string) ($this->designCatalog->getDefinitions()[$designKey]['name'] ?? '');
        $id = (int) $this->designCollectionFactory->create()
            ->addFieldToFilter('is_system', ['eq' => 1])
            ->addFieldToFilter('name', ['eq' => $name])
            ->getFirstItem()
            ->getData('design_id');
        if ($id === 0) {
            throw new LocalizedException(
                __('The built-in design "%1" is missing. Run bin/magento setup:upgrade to restore it.', $name)
            );
        }

        return $id;
    }

    /**
     * @param string $name
     * @return LabelInterface|null
     */
    private function recentLabel(string $name): ?LabelInterface
    {
        $id = (int) $this->labelCollectionFactory->create()
            ->addFieldToFilter('name', ['eq' => $name])
            ->addFieldToFilter('created_at', ['gteq' => gmdate('Y-m-d H:i:s', time() - self::DOUBLE_SUBMIT_SECONDS)])
            ->getFirstItem()
            ->getData('label_id');

        return $id > 0 ? $this->labelRepository->getById($id) : null;
    }
}
