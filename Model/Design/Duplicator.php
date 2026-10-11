<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Model\Design;

use Iranimij\OpenLabel\Api\Data\DesignInterface;
use Iranimij\OpenLabel\Api\DesignRepositoryInterface;
use Iranimij\OpenLabel\Model\DesignFactory;

/**
 * Copies a design (including its store texts) into a new, editable one: "Duplicate to edit" for built-ins.
 */
class Duplicator
{
    /**
     * @param DesignFactory $designFactory
     * @param DesignRepositoryInterface $designRepository
     */
    public function __construct(
        private readonly DesignFactory $designFactory,
        private readonly DesignRepositoryInterface $designRepository
    ) {
    }

    /**
     * @param DesignInterface $source
     * @return DesignInterface the saved copy
     */
    public function duplicate(DesignInterface $source): DesignInterface
    {
        $copy = $this->designFactory->create();
        $copy->setName((string) __('%1 (copy)', $source->getName()))
            ->setType($source->getType())
            ->setShape($source->getShape())
            ->setImagePath($source->getImagePath())
            ->setImageWidth($source->getImageWidth())
            ->setImageHeight($source->getImageHeight())
            ->setBgColor($source->getBgColor())
            ->setTextColor($source->getTextColor())
            ->setBorderColor($source->getBorderColor())
            ->setBorderWidth($source->getBorderWidth())
            ->setFontSize($source->getFontSize())
            ->setSizeMode($source->getSizeMode())
            ->setWidth($source->getWidth())
            ->setHeight($source->getHeight())
            ->setOpacity($source->getOpacity())
            ->setRotation($source->getRotation())
            ->setCustomCss($source->getCustomCss())
            ->setIsSystem(false)
            ->setStoreTexts($source->getStoreTexts());

        return $this->designRepository->save($copy);
    }
}
