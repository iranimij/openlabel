<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Model\Image;

use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem\Driver\File;

/**
 * Checks an uploaded label image: extension allow-list, real content type, size limits (warn over 50 KB,
 * refuse over 1 MB), intrinsic dimensions (stored for CLS-free rendering, FE6). SVGs are sanitized.
 */
class Validator
{
    public const WARN_BYTES = 50 * 1024;
    public const MAX_BYTES = 1024 * 1024;

    /** Extension => accepted MIME types. */
    private const TYPES = [
        'png' => ['image/png'],
        'jpg' => ['image/jpeg'],
        'gif' => ['image/gif'],
        'webp' => ['image/webp'],
        'svg' => ['image/svg+xml', 'text/xml', 'application/xml', 'text/plain', 'text/html'],
    ];

    /**
     * @param SvgSanitizer $svgSanitizer
     * @param File $fileDriver
     */
    public function __construct(
        private readonly SvgSanitizer $svgSanitizer,
        private readonly File $fileDriver
    ) {
    }

    /**
     * @param string $path uploaded temporary file
     * @param string $originalName file name as sent by the browser
     * @return ValidationResult
     */
    public function validate(string $path, string $originalName): ValidationResult
    {
        $dot = strrpos($originalName, '.');
        $extension = $dot === false ? '' : strtolower(substr($originalName, $dot + 1));
        $extension = $extension === 'jpeg' ? 'jpg' : $extension;
        if (!isset(self::TYPES[$extension])) {
            return new ValidationResult([__('Allowed image types are PNG, JPG, GIF, WebP and SVG.')]);
        }
        try {
            $size = (int) ($this->fileDriver->stat($path)['size'] ?? 0);
            $contents = (string) $this->fileDriver->fileGetContents($path);
        } catch (FileSystemException $e) {
            return $this->mismatch();
        }
        if ($size > self::MAX_BYTES) {
            return new ValidationResult([__('The image is larger than 1 MB. Use a smaller file; SVG works best.')]);
        }
        $warnings = $size > self::WARN_BYTES
            ? [__('The image is larger than 50 KB (%1 KB). Smaller files keep listing pages fast.', (int) ceil($size / 1024))]
            : [];
        $mime = (string) (new \finfo(FILEINFO_MIME_TYPE))->file($path);
        if (!in_array($mime, self::TYPES[$extension], true)) {
            return $this->mismatch();
        }

        if ($extension === 'svg') {
            try {
                $clean = $this->svgSanitizer->sanitize($contents);
            } catch (LocalizedException $e) {
                return new ValidationResult([$e->getRawMessage() === '' ? __('The file is not a valid SVG image.') : __($e->getRawMessage())]);
            }
            [$width, $height] = $this->svgSanitizer->dimensions($clean);

            return new ValidationResult([], $warnings, 'svg', $width, $height, $clean);
        }

        $info = $contents === '' ? false : getimagesizefromstring($contents);
        if ($info === false || $info[0] < 1 || $info[1] < 1) {
            return $this->mismatch();
        }

        return new ValidationResult([], $warnings, $extension, (int) $info[0], (int) $info[1]);
    }

    /**
     * @return ValidationResult
     */
    private function mismatch(): ValidationResult
    {
        return new ValidationResult([
            __('The file content does not match its extension. Upload a real PNG, JPG, GIF, WebP or SVG image.'),
        ]);
    }
}
