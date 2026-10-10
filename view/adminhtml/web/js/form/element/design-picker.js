/**
 * Copyright © Iman Aboheydary. All rights reserved.
 * See LICENSE for license details (MIT).
 */

/**
 * Visual design picker: thumbnails instead of a drop-down, a link to create a design in a new tab and a
 * refresh button to pick it up afterwards (08 · UX Spec §4).
 */
define([
    'jquery',
    'Magento_Ui/js/form/element/abstract'
], function ($, Abstract) {
    'use strict';

    return Abstract.extend({
        defaults: {
            designs: [],
            createUrl: '',
            refreshUrl: '',
            elementTmpl: 'Iranimij_OpenLabel/form/element/design-picker'
        },

        /** @inheritdoc */
        initObservable: function () {
            this._super().observe(['designs']);

            return this;
        },

        /**
         * @param {Object} design
         */
        choose: function (design) {
            this.value(design.value);
        },

        /**
         * @param {Object} design
         * @returns {Boolean}
         */
        isChosen: function (design) {
            return String(this.value()) === String(design.value);
        },

        /** @returns {Object|undefined} the chosen design */
        chosen: function () {
            var value = String(this.value());

            return this.designs().filter(function (design) {
                return String(design.value) === value;
            })[0];
        },

        /** Reloads the list (after a design was created in another tab). */
        refresh: function () {
            $.getJSON(this.refreshUrl, {isAjax: true}).done(function (response) {
                this.designs(response.designs || []);
            }.bind(this));
        }
    });
});
