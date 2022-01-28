/*
 * Copyright (c) 2018 MageModule, LLC: All rights reserved
 *
 * LICENSE: This source file is subject to our standard End User License
 * Agreeement (EULA) that is available through the world-wide-web at the
 * following URI: https://www.magemodule.com/end-user-license-agreement/.
 *
 *  If you did not receive a copy of the EULA and are unable to obtain it through
 *  the web, please send a note to admin@magemodule.com so that we can mail
 *  you a copy immediately.
 *
 *  @author        MageModule admin@magemodule.com
 *  @copyright    2018 MageModule, LLC
 *  @license        https://www.magemodule.com/end-user-license-agreement/
 */

define([
    'Magento_Ui/js/form/element/abstract',
    'underscore',
    'uiRegistry'
], function (Abstract, _, registry) {
    'use strict';

    return Abstract.extend({
        defaults: {
            allowImport: true,
            autoImportIfEmpty: false,
            values: {},
            mask: '',
            queryTemplate: 'ns = ${ $.ns }, index = '
        },

        /** @inheritdoc */
        initialize: function () {
            this._super();

            if (this.allowImport) {
                this.setHandlers();
            }
        },

        /**
         * Split mask placeholder and attach events to placeholder fields.
         */
        setHandlers: function () {
            var str = this.mask || '',
                placeholders;

            placeholders = str.match(/{{(.*?)}}/g); // Get placeholders

            _.each(placeholders, function (placeholder) {
                // noinspection Annotator
                placeholder = placeholder.replace(/[{{}}]/g, ''); // Remove curly braces

                registry.get(this.queryTemplate + placeholder, function (component) {
                    this.values[placeholder] = component.getPreview();
                    component.on('value', this.updateValue.bind(this, placeholder, component));
                    component.valueUpdate = 'keyup';
                }.bind(this));
            }, this);
        },

        /**
         * Update field with mask value, if it's allowed.
         *
         * @param {Object} placeholder
         * @param {Object} component
         */
        updateValue: function (placeholder, component) {
            var string = this.mask || '',
                nonEmptyValueFlag = false;

            if (placeholder) {
                this.values[placeholder] = component.getPreview() || '';
            }

            if (!this.allowImport) {
                return;
            }

            _.each(this.values, function (propertyValue, propertyName) {
                string = string.replace('{{' + propertyName + '}}', propertyValue);
                nonEmptyValueFlag = nonEmptyValueFlag || !!propertyValue;
            });

            if (nonEmptyValueFlag) {
                string = string.replace(/(<([^>]+)>)/ig, ''); // Remove html tags
                this.value(string);
            } else {
                this.value('');
            }
        },

        /**
         * Disallow import when initial value isn't empty string
         *
         * @returns {*}
         */
        setInitialValue: function () {
            this._super();

            if (this.initialValue !== '') {
                this.allowImport = false;
            }

            return this;
        },

        /**
         *  Callback when value is changed by user,
         *  and disallow/allow import value
         */
        userChanges: function () {

            /**
             *  As userChanges is called before updateValue,
             *  we forced to get value from component by reference
             */
            var actualValue = arguments[1].currentTarget.value;

            this._super();

            if (actualValue === '') {
                this.allowImport = true;

                if (this.autoImportIfEmpty) {
                    this.updateValue(null, null);
                }
            } else {
                this.allowImport = false;
            }
        }
    });
});
