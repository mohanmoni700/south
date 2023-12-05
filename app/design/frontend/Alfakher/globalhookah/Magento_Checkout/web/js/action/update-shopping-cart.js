/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/modal/alert',
    'jquery',
    'jquery-ui-modules/widget',
    'mage/validation'
], function (alert, $) {
    'use strict';

    $.widget('mage.updateShoppingCart', {
        options: {
            validationURL: '',
            eventName: 'updateCartItemQty',
            updateCartActionContainer: '',
            messageCookieName: 'update_cart_message'
        },

        /** @inheritdoc */
        _create: function () {
            this._on(this.element, {
                'submit': this.onSubmit
            });

            this.initialFormData = this.element.serialize();
            // Check for stored messages and display them
            let storedMessage = this.getStoredMessage();
            if (storedMessage) {
                this.showPageMessage(storedMessage.type, storedMessage.content);
                this.clearStoredMessage();
            }
        },

        /**
         * Prevents default submit action and calls form validator.
         *
         * @param {Event} event
         * @return {Boolean}
         */
        onSubmit: function (event) {
            let action = this.element.find(this.options.updateCartActionContainer).val();

            if (!this.options.validationURL || action === 'empty_cart') {
                return true;
            }

            if (this.isValid() && this.hasFormDataChanged()) {
                event.preventDefault();
                this.validateItems(this.options.validationURL, this.element.serialize());
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
            let successMessage = 'The requested quantity is updated.';
            this.storeMessage('success', successMessage);
            this.submitForm();
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
        },

        /**
         * Store a message in a cookie.
         *
         * @param {String} type - Message type (e.g., 'success', 'error').
         * @param {String} content - Message content.
         */
        storeMessage: function (type, content) {
            $.mage.cookies.set(this.options.messageCookieName, JSON.stringify({ type: type, content: content }), { lifetime: 3600 });
        },

        /**
         * Get the stored message from the cookie.
         *
         * @returns {Object|null} - Stored message, or null if not found.
         */
        getStoredMessage: function () {
            let storedMessage = $.mage.cookies.get(this.options.messageCookieName);
            return storedMessage ? JSON.parse(storedMessage) : null;
        },

        /**
         * Clear the stored message from the cookie.
         */
        clearStoredMessage: function () {
            $.mage.cookies.set(this.options.messageCookieName, '', { lifetime: -1 });
        },

        /**
         * Displays a page message.
         *
         * @param {String} type - Message type (e.g., 'success', 'error').
         * @param {String} content - Message content.
         */
        showPageMessage: function (type, content) {
            $(".page.messages").html('<div class="message-' + type + ' ' + type +' message">' +
                '<div>' + content + '</div></div>');
            $(".page.messages").delay(200).fadeIn().delay(4000).fadeOut();
        }
    });

    return $.mage.updateShoppingCart;
});
