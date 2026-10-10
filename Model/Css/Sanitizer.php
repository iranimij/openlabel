<?php
/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

declare(strict_types=1);

namespace Iranimij\OpenLabel\Model\Css;

/**
 * Cleans merchant custom CSS before it is stored and appended to the generated stylesheet (02 · Architecture §8).
 * Removes markup, comments, at-rules and every declaration that can execute code or load foreign resources:
 * expression(), behavior, -moz-binding, javascript:/vbscript:/data: URLs and CSS escapes (which could spell them).
 */
class Sanitizer
{
    /** One value token: anything but separators, or a parenthesised group with one level of nesting. */
    private const TOKEN = '(?:[^;{}()]|\((?:[^()]|\([^()]*\))*\))';

    private const DANGER = [
        'expression\s*\(',
        'behavior\s*:',
        '-moz-binding',
        'url\s*\(\s*[\'"]?\s*(?:javascript|vbscript|data)\s*:',
        'javascript\s*:',
        '\\\\',
    ];

    /**
     * @param string|null $css
     * @return string|null null when nothing is left
     */
    public function sanitize(?string $css): ?string
    {
        $css = (string) $css;
        $css = (string) preg_replace('#<[^>]*>#', '', $css);
        $css = str_replace('<', '', $css);
        $css = (string) preg_replace('#/\*.*?(\*/|$)#s', '', $css);
        $css = (string) preg_replace('/@[a-z-]+[^;{]*(;|(?=\{))/i', '', $css);

        $declaration = '/' . self::TOKEN . '*?(?:' . implode('|', self::DANGER) . ')' . self::TOKEN . '*;?/i';
        do {
            $before = $css;
            $css = (string) preg_replace($declaration, '', $css);
        } while ($css !== $before);

        return trim($css) === '' ? null : $css;
    }
}
