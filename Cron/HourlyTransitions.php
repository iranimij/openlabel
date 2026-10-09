<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Cron;

use Iranimij\OpenLabel\Api\Data\LabelInterface;
use Iranimij\OpenLabel\Model\Cache\Invalidator;
use Iranimij\OpenLabel\Model\Indexer\LabelReindexer;
use Iranimij\OpenLabel\Model\ResourceModel\Label\CollectionFactory as LabelCollectionFactory;
use Magento\Framework\Stdlib\DateTime\DateTime;

/**
 * Time windows live in the index and are filtered at resolve time; cached pages only need cleaning when a
 * label's window opened or closed during the last hour (06 · F21, documented one-hour worst case).
 */
class HourlyTransitions
{
    private const WINDOW_SECONDS = 3600;

    /**
     * @param LabelCollectionFactory $labelCollectionFactory
     * @param LabelReindexer $labelReindexer
     * @param Invalidator $invalidator
     * @param DateTime $dateTime
     */
    public function __construct(
        private readonly LabelCollectionFactory $labelCollectionFactory,
        private readonly LabelReindexer $labelReindexer,
        private readonly Invalidator $invalidator,
        private readonly DateTime $dateTime
    ) {
    }

    /**
     * @return void
     */
    public function execute(): void
    {
        $now = $this->dateTime->gmtDate();
        $from = $this->dateTime->gmtDate(null, $this->dateTime->gmtTimestamp() - self::WINDOW_SECONDS);
        $collection = $this->labelCollectionFactory->create();
        $collection->addFieldToFilter(
            [LabelInterface::VALID_FROM, LabelInterface::VALID_TO],
            [['from' => $from, 'to' => $now], ['from' => $from, 'to' => $now]]
        );
        foreach ($collection->getItems() as $label) {
            $labelId = (int) $label->getLabelId();
            $this->invalidator->clean($this->labelReindexer->productIds($labelId), [$labelId]);
        }
    }
}
