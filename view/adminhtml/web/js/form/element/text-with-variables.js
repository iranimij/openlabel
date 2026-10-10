/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

/**
 * Label text input with a variable picker (inserts {SAVE_PERCENT} etc. with a one-line description) and a
 * character counter against the 24-character soft limit (08 · UX Spec §2, §5).
 */
define([
    'Magento_Ui/js/form/element/abstract',
    'mage/translate'
], function (Abstract, $t) {
    'use strict';

    return Abstract.extend({
        defaults: {
            variables: [],
            softLimit: 24,
            selectedVariable: ''
        },

        /** @inheritdoc */
        initObservable: function () {
            this._super().observe(['selectedVariable']);
            this.selectedVariable.subscribe(this.insertVariable, this);

            return this;
        },

        /** Inserts the picked variable at the end of the text. */
        insertVariable: function () {
            var token = this.selectedVariable(),
                current = String(this.value() || '');

            if (!token) {
                return;
            }
            this.value(current + (current && !/\s$/.test(current) ? ' ' : '') + token);
            this.selectedVariable('');
        },

        /** @returns {Object|undefined} the picked variable */
        pickedVariable: function () {
            var token = this.selectedVariable();

            return this.variables.filter(function (variable) {
                return variable.token === token;
            })[0];
        },

        /** @returns {Number} */
        length: function () {
            return String(this.value() || '').length;
        },

        /** @returns {String} */
        counterText: function () {
            return $t('%1 / %2 characters').replace('%1', this.length()).replace('%2', this.softLimit);
        },

        /** @returns {Boolean} */
        isLong: function () {
            return this.length() > this.softLimit;
        }
    });
});
