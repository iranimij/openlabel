<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Model\Image;

use Magento\Framework\Exception\LocalizedException;

/**
 * Allow-list SVG sanitizer for uploaded label images (02 · Architecture §8): no DOCTYPE or entities, only
 * drawing elements, no event handlers, no external or script references, no url() in inline styles.
 */
class SvgSanitizer
{
    private const SVG_NS = 'http://www.w3.org/2000/svg';
    private const XLINK_NS = 'http://www.w3.org/1999/xlink';

    private const ELEMENTS = [
        'svg', 'g', 'path', 'rect', 'circle', 'ellipse', 'line', 'polyline', 'polygon', 'text', 'tspan',
        'defs', 'lineargradient', 'radialgradient', 'stop', 'title', 'desc', 'clippath', 'mask', 'use', 'symbol',
    ];

    /**
     * @param string $svg
     * @return string sanitized markup
     * @throws LocalizedException when the file is not a plain SVG document
     */
    public function sanitize(string $svg): string
    {
        $document = $this->load($svg);
        $root = $document->documentElement;
        if ($root === null) {
            throw new LocalizedException(__('The file is not a valid SVG image.'));
        }
        $this->clean($root);

        return (string) $document->saveXML($root);
    }

    /**
     * Intrinsic size from width/height (unitless or px) or the viewBox.
     *
     * @param string $svg
     * @return array{?int, ?int}
     */
    public function dimensions(string $svg): array
    {
        try {
            $root = $this->load($svg)->documentElement;
        } catch (LocalizedException $e) {
            return [null, null];
        }
        if ($root === null) {
            return [null, null];
        }
        $width = $this->length($root->getAttribute('width'));
        $height = $this->length($root->getAttribute('height'));
        if ($width === null || $height === null) {
            $box = preg_split('/[\s,]+/', trim($root->getAttribute('viewBox'))) ?: [];
            if (count($box) === 4 && (float) $box[2] > 0 && (float) $box[3] > 0) {
                return [(int) round((float) $box[2]), (int) round((float) $box[3])];
            }

            return [null, null];
        }

        return [$width, $height];
    }

    /**
     * @param string $svg
     * @return \DOMDocument
     * @throws LocalizedException
     */
    private function load(string $svg): \DOMDocument
    {
        if (preg_match('/<!DOCTYPE|<!ENTITY/i', $svg)) {
            throw new LocalizedException(__('SVG files with a DOCTYPE or entities are not accepted.'));
        }
        $document = new \DOMDocument();
        $previous = libxml_use_internal_errors(true);
        $loaded = $document->loadXML($svg, LIBXML_NONET | LIBXML_NOBLANKS);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);
        $root = $loaded ? $document->documentElement : null;
        if ($root === null || strtolower($root->localName) !== 'svg') {
            throw new LocalizedException(__('The file is not a valid SVG image.'));
        }

        return $document;
    }

    /**
     * @param \DOMElement $element
     * @return void
     */
    private function clean(\DOMElement $element): void
    {
        foreach (iterator_to_array($element->attributes) as $attribute) {
            /** @var \DOMAttr $attribute */
            if ($this->isUnsafeAttribute($attribute)) {
                $element->removeAttributeNode($attribute);
            }
        }
        foreach (iterator_to_array($element->childNodes) as $child) {
            if ($child instanceof \DOMElement) {
                $allowed = in_array(strtolower($child->localName), self::ELEMENTS, true)
                    && in_array($child->namespaceURI, [self::SVG_NS, null], true);
                if (!$allowed) {
                    $element->removeChild($child);
                    continue;
                }
                $this->clean($child);
            } elseif ($child instanceof \DOMProcessingInstruction || $child instanceof \DOMComment) {
                $element->removeChild($child);
            }
        }
    }

    /**
     * @param \DOMAttr $attribute
     * @return bool
     */
    private function isUnsafeAttribute(\DOMAttr $attribute): bool
    {
        $name = strtolower($attribute->localName);
        $value = strtolower(preg_replace('/\s+/', '', $attribute->value) ?? '');
        if (str_starts_with($name, 'on')) {
            return true;
        }
        if ($name === 'href' && ($attribute->namespaceURI === null || $attribute->namespaceURI === self::XLINK_NS)) {
            return !str_starts_with($attribute->value, '#');
        }
        if (str_contains($value, 'javascript:') || str_contains($value, 'expression(')) {
            return true;
        }

        preg_match_all('/url\([\'"]?([^)\'"]*)/', $value, $targets);
        foreach ($targets[1] as $target) {
            if (!str_starts_with($target, '#')) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $value
     * @return int|null
     */
    private function length(string $value): ?int
    {
        return preg_match('/^\s*(\d+(?:\.\d+)?)\s*(px)?\s*$/i', $value, $match) ? (int) round((float) $match[1]) : null;
    }
}
