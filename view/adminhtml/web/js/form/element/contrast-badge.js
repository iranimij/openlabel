/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

/**
 * Contrast ratio badge for the design colours: green ≥ 4.5, amber ≥ 3, red below (08 · UX Spec §5).
 */
define([
    'uiElement',
    'Iranimij_OpenLabel/js/contrast',
    'mage/translate'
], function (Element, contrast, $t) {
    'use strict';

    return Element.extend({
        defaults: {
            background: '',
            foreground: '',
            ratio: null,
            level: 'unknown'
        },

        /** @inheritdoc */
        initObservable: function () {
            this._super().observe(['background', 'foreground', 'ratio', 'level']);
            this.background.subscribe(this.update, this);
            this.foreground.subscribe(this.update, this);

            return this;
        },

        /** @inheritdoc */
        initialize: function () {
            this._super();
            this.update();

            return this;
        },

        /** Recomputes the ratio. */
        update: function () {
            var ratio = contrast.ratio(this.foreground(), this.background());

            this.level(contrast.level(ratio));
            this.ratio(ratio);
        },

        /** @returns {String} */
        text: function () {
            var ratio = this.ratio(),
                messages = {
                    pass: $t('Contrast %1:1 · easy to read'),
                    large: $t('Contrast %1:1 · only readable for large text, choose stronger colours'),
                    fail: $t('Contrast %1:1 · too low, many shoppers cannot read this')
                };

            if (ratio === null || !messages[this.level()]) {
                return $t('Pick a background and a text colour to check the contrast.');
            }

            return messages[this.level()].replace('%1', ratio.toFixed(1));
        }
    });
});
