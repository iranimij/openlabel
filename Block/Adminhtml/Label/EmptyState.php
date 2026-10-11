<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Block\Adminhtml\Label;

use Iranimij\OpenLabel\Model\Design\SystemDesignCatalog;
use Iranimij\OpenLabel\Model\Design\Thumbnail;
use Iranimij\OpenLabel\Model\ResourceModel\Label\CollectionFactory;
use Iranimij\OpenLabel\Model\Starter\Catalog;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;

/**
 * Empty state of the labels grid: three one-click starters (08 · UX Spec §3) and the feedback link (09 · P5).
 * Renders nothing once a label exists.
 */
class EmptyState extends Template
{
    public const FEEDBACK_URL = 'https://github.com/iranimij/openlabel/discussions';

    /**
     * @var string
     */
    protected $_template = 'Iranimij_OpenLabel::label/empty-state.phtml';

    /**
     * @param Context $context
     * @param CollectionFactory $labelCollectionFactory
     * @param Catalog $starterCatalog
     * @param SystemDesignCatalog $designCatalog
     * @param Thumbnail $thumbnail
     * @param array<string, mixed> $data
     */
    public function __construct(
        Context $context,
        private readonly CollectionFactory $labelCollectionFactory,
        private readonly Catalog $starterCatalog,
        private readonly SystemDesignCatalog $designCatalog,
        private readonly Thumbnail $thumbnail,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * @return array<string, array{title: string, hint: string, thumb: string}>
     */
    public function getStarters(): array
    {
        $designs = $this->designCatalog->getDefinitions();
        $starters = [];
        foreach ($this->starterCatalog->getStarters() as $key => $starter) {
            $design = $designs[$starter['design']];
            $starters[$key] = [
                'title' => $starter['title'],
                'hint' => $starter['hint'],
                'thumb' => $this->thumbnail->render($design + ['text' => $design['store_texts'][0]['text']]),
            ];
        }

        return $starters;
    }

    /**
     * @return string
     */
    public function getCreateUrl(): string
    {
        return $this->getUrl('openlabel/starter/create');
    }

    /**
     * @inheritDoc
     */
    protected function _toHtml()
    {
        return $this->labelCollectionFactory->create()->getSize() > 0 ? '' : parent::_toHtml();
    }
}
