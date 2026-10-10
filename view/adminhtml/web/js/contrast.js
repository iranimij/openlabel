/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

/**
 * WCAG 2.x contrast ratio between two hex colours. Same formula as Model\Design\Contrast (server side).
 */
define([], function () {
    'use strict';

    function luminance(hex) {
        var match = /^#([0-9a-f]{3}|[0-9a-f]{6}|[0-9a-f]{8})$/i.exec(String(hex || '').trim()),
            digits;

        if (!match) {
            return null;
        }
        digits = match[1];
        if (digits.length === 3) {
            digits = digits[0] + digits[0] + digits[1] + digits[1] + digits[2] + digits[2];
        }

        return [0, 2, 4].map(function (offset) {
            var value = parseInt(digits.substr(offset, 2), 16) / 255;

            return value <= 0.03928 ? value / 12.92 : Math.pow((value + 0.055) / 1.055, 2.4);
        }).reduce(function (sum, channel, index) {
            return sum + channel * [0.2126, 0.7152, 0.0722][index];
        }, 0);
    }

    return {
        /**
         * @param {String} foreground
         * @param {String} background
         * @returns {Number|null}
         */
        ratio: function (foreground, background) {
            var fg = luminance(foreground),
                bg = luminance(background);

            if (fg === null || bg === null) {
                return null;
            }

            return (Math.max(fg, bg) + 0.05) / (Math.min(fg, bg) + 0.05);
        },

        /**
         * @param {Number|null} ratio
         * @returns {String} pass (≥ 4.5), large (≥ 3), fail or unknown
         */
        level: function (ratio) {
            if (ratio === null) {
                return 'unknown';
            }

            return ratio >= 4.5 ? 'pass' : ratio >= 3 ? 'large' : 'fail';
        }
    };
});
