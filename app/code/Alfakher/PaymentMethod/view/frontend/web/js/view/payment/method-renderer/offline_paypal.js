define(
    [
        'jquery',
        'Magento_Checkout/js/view/payment/default',
        'mage/url',
        'Magento_Checkout/js/model/error-processor',
        'Magento_Checkout/js/action/redirect-on-success',
        'Magento_Checkout/js/model/quote',
        'mage/validation'
    ],
    function ($,Component,url, errorProcessor,redirectOnSuccessAction, quote) {
        'use strict';
        
        return Component.extend({
            defaults: {
                template: 'Alfakher_PaymentMethod/payment/offline_paypal',
                paypalEmail:'',
            },
            /** @inheritdoc */
            initObservable: function () {
                this._super()
                    .observe(['paypalEmail']);

                return this;
            },
            /**
         * @return {Object}
         */
        getData: function () {
            return {
                method: this.item.method,
                'additional_data': {
                    'paypalemail': $('#paypal_email').val()
                }
            };
        },
        /**
         * @return {jQuery}
         */
        validate: function () {
            var form = 'form[data-role=paypalemail_form]';

            return $(form).validation() && $(form).validation('isValid');
        },
       
        });
    }
);