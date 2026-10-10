<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Model\Image;

use Magento\Framework\Phrase;

/**
 * Outcome of checking an uploaded label image.
 */
class ValidationResult
{
    /**
     * @param Phrase[] $errors
     * @param Phrase[] $warnings
     * @param string|null $extension normalised extension (jpg for jpeg)
     * @param int|null $width
     * @param int|null $height
     * @param string|null $sanitizedContents SVG markup to store instead of the upload
     */
    public function __construct(
        private readonly array $errors,
        private readonly array $warnings = [],
        private readonly ?string $extension = null,
        private readonly ?int $width = null,
        private readonly ?int $height = null,
        private readonly ?string $sanitizedContents = null
    ) {
    }

    /**
     * @return Phrase[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @return Phrase[]
     */
    public function getWarnings(): array
    {
        return $this->warnings;
    }

    /**
     * @return string|null
     */
    public function getExtension(): ?string
    {
        return $this->extension;
    }

    /**
     * @return int|null
     */
    public function getWidth(): ?int
    {
        return $this->width;
    }

    /**
     * @return int|null
     */
    public function getHeight(): ?int
    {
        return $this->height;
    }

    /**
     * @return string|null
     */
    public function getSanitizedContents(): ?string
    {
        return $this->sanitizedContents;
    }
}
