/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

/**
 * The visual 3×3 position picker for a placement (08 · UX Spec §4): nine buttons laid out like the product image.
 * Arrow keys move the choice, so it works without a mouse.
 */
define([
    'Magento_Ui/js/form/element/select'
], function (Select) {
    'use strict';

    var GRID = ['tl', 'tc', 'tr', 'ml', 'mc', 'mr', 'bl', 'bc', 'br'];

    return Select.extend({
        defaults: {
            elementTmpl: 'Iranimij_OpenLabel/form/element/position-picker'
        },

        /** @returns {Array} options in grid order */
        cells: function () {
            var byValue = {};

            this.options().forEach(function (option) {
                byValue[option.value] = option;
            });

            return GRID.map(function (value) {
                return byValue[value] || {value: value, label: value};
            });
        },

        /**
         * @param {Object} cell
         */
        choose: function (cell) {
            this.value(cell.value);
        },

        /**
         * @param {Object} cell
         * @returns {Boolean}
         */
        isChosen: function (cell) {
            return String(this.value()) === String(cell.value);
        },

        /**
         * Arrow-key navigation inside the grid.
         *
         * @param {Object} cell
         * @param {Object} data knockout passes the bound context first
         * @param {jQuery.Event} event
         * @returns {Boolean}
         */
        onKey: function (cell, data, event) {
            var index = GRID.indexOf(cell.value),
                moves = {ArrowLeft: -1, ArrowRight: 1, ArrowUp: -3, ArrowDown: 3},
                next;

            if (!moves.hasOwnProperty(event.key)) {
                return true;
            }
            next = index + moves[event.key];
            if (next >= 0 && next < GRID.length &&
                !(event.key === 'ArrowLeft' && index % 3 === 0) &&
                !(event.key === 'ArrowRight' && index % 3 === 2)
            ) {
                this.value(GRID[next]);
                event.currentTarget.parentNode.children[next].focus();
            }

            return false;
        }
    });
});
