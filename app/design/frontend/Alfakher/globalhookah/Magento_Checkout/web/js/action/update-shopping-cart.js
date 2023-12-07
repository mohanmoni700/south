/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/modal/alert',
    'Magento_Ui/js/modal/confirm',
    'jquery',
    'jquery-ui-modules/widget',
    'mage/validation'
], function (alert, confirm, $) {
    'use strict';

    $.widget('mage.updateShoppingCart', {
        options: {
            validationURL: '',
            message: '',
            eventName: 'updateCartItemQty',
            updateCartActionContainer: ''
        },

        /** @inheritdoc */
        _create: function () {
            this._on(this.element, {
                'submit': this.onSubmit
            });

            this.initialFormData = this.element.serialize();
        },

        /**
         * Prevents default submit action and calls form validator.
         *
         * @param {Event} event
         * @return {Boolean}
         */
        onSubmit: function (event) {
            let action = this.element.find(this.options.updateCartActionContainer).val();
            let self = this;

            if (!self.options.validationURL || action === 'empty_cart') {
                return true;
            }

            if (self.isValid() && self.hasFormDataChanged()) {
                confirm({
                    content: "Are you sure you would like to update the shopping cart?",
                    actions: {
                        /** @inheritdoc */
                        confirm: function () {
                            event.preventDefault();
                            self.validateItems(self.options.validationURL, self.element.serialize());
                        },
                    }
                });
            }

            return false;
        },


        /**
         * Checks if form is updated.
         *
         * @return {Boolean}
         */
        hasFormDataChanged: function () {
            let currentFormData = this.element.serialize();
            return this.initialFormData !== currentFormData;
        },

        /**
         * Validates requested form.
         *
         * @return {Boolean}
         */
        isValid: function () {
            return this.element.validation() && this.element.validation('isValid');
        },

        /**
         * Validates updated shopping cart data.
         *
         * @param {String} url - request url
         * @param {Object} data - post data for ajax call
         */
        validateItems: function (url, data) {
            $.extend(data, {
                'form_key': $.mage.cookies.get('form_key')
            });

            $.ajax({
                url: url,
                data: data,
                type: 'post',
                dataType: 'json',
                context: this,

                /** @inheritdoc */
                beforeSend: function () {
                    $(document.body).trigger('processStart');
                },

                /** @inheritdoc */
                complete: function () {
                    $(document.body).trigger('processStop');
                }
            })
                .done(function (response) {
                    if (response.success) {
                        this.onSuccess();
                    } else {
                        this.onError(response);
                    }
                })
                .fail(function () {
                    this.submitForm();
                });
        },

        /**
         * Form validation succeed.
         */
        onSuccess: function () {
            this.submitForm();
            $('.page.messages').show();
        },

        /**
         * Form validation failed.
         */
        onError: function (response) {
            if (response['error_message']) {
                alert({
                    content: response['error_message']
                });
            } else {
                this.submitForm();
            }
        },

        /**
         * Real submit of validated form.
         */
        submitForm: function () {
            this.element
                .off('submit', this.onSubmit)
                .on('submit', function () {
                    $(document.body).trigger('processStart');
                })
                .trigger('submit');
        }
    });

    return $.mage.updateShoppingCart;
});
