<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Model\Css;

use Iranimij\OpenLabel\Api\Data\DesignInterface;
use Iranimij\OpenLabel\Api\Data\PlacementInterface;
use Iranimij\OpenLabel\Model\ResourceModel\Design\CollectionFactory as DesignCollectionFactory;
use Iranimij\OpenLabel\Model\ResourceModel\Placement\CollectionFactory as PlacementCollectionFactory;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Module\Dir;
use Magento\Framework\Module\Dir\Reader;

/**
 * Builds the one OpenLabel stylesheet (10 · Front-end Review FE2): the module's structural CSS, one custom-property
 * line per design, one per placement that moves away from the defaults, then the sanitized custom CSS of each design.
 */
class StylesheetGenerator
{
    public const STRUCTURAL_SOURCE = 'base/web/css/openlabel.css';

    /**
     * @param DesignRules $designRules
     * @param Sanitizer $sanitizer
     * @param DesignCollectionFactory $designCollectionFactory
     * @param PlacementCollectionFactory $placementCollectionFactory
     * @param Reader $moduleReader
     * @param File $file
     */
    public function __construct(
        private readonly DesignRules $designRules,
        private readonly Sanitizer $sanitizer,
        private readonly DesignCollectionFactory $designCollectionFactory,
        private readonly PlacementCollectionFactory $placementCollectionFactory,
        private readonly Reader $moduleReader,
        private readonly File $file
    ) {
    }

    /**
     * @return string the stylesheet for the current designs and placements
     */
    public function build(): string
    {
        $designs = $this->designCollectionFactory->create()->setOrder(DesignInterface::DESIGN_ID, 'ASC');
        $placements = $this->placementCollectionFactory->create()->setOrder(PlacementInterface::PLACEMENT_ID, 'ASC');

        /** @var DesignInterface[] $designItems */
        $designItems = $designs->getItems();
        /** @var PlacementInterface[] $placementItems */
        $placementItems = $placements->getItems();

        return $this->compose($this->structuralCss(), $designItems, $placementItems);
    }

    /**
     * @param string $structural
     * @param DesignInterface[] $designs
     * @param PlacementInterface[] $placements
     * @return string
     */
    public function compose(string $structural, array $designs, array $placements): string
    {
        usort($designs, fn (DesignInterface $a, DesignInterface $b): int
            => (int) $a->getDesignId() <=> (int) $b->getDesignId());
        usort($placements, fn (PlacementInterface $a, PlacementInterface $b): int
            => (int) $a->getPlacementId() <=> (int) $b->getPlacementId());

        $lines = ['/* OpenLabel · generated, do not edit · https://github.com/iranimij/openlabel */', $this->minify($structural)];
        foreach ($designs as $design) {
            $rule = $this->designRules->forDesign($design);
            if ($rule !== '') {
                $lines[] = $rule;
            }
        }
        foreach ($placements as $placement) {
            $rule = $this->designRules->forPlacement($placement);
            if ($rule !== null) {
                $lines[] = $rule;
            }
        }
        foreach ($designs as $design) {
            $custom = trim((string) $this->sanitizer->sanitize($design->getCustomCss()));
            if ($custom !== '') {
                $lines[] = '/* design ' . (int) $design->getDesignId() . " */\n" . $custom;
            }
        }

        return implode("\n", $lines) . "\n";
    }

    /**
     * @return string
     */
    private function structuralCss(): string
    {
        $path = $this->moduleReader->getModuleDir(Dir::MODULE_VIEW_DIR, 'Iranimij_OpenLabel')
            . '/' . self::STRUCTURAL_SOURCE;

        return $this->file->fileGetContents($path);
    }

    /**
     * Comments and whitespace out; never touches spaces around ":" (descendant selectors with pseudo-classes).
     *
     * @param string $css
     * @return string
     */
    private function minify(string $css): string
    {
        $css = (string) preg_replace('#/\*.*?\*/#s', '', $css);
        $css = (string) preg_replace('/\s+/', ' ', $css);
        $css = (string) preg_replace('/\s*([{};,>])\s*/', '$1', $css);

        return trim(str_replace(';}', '}', $css));
    }
}
