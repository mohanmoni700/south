define(
    [
        'jquery',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/model/quote',
        'mage/url',
        'Magento_Customer/js/model/customer',
        'underscore',
        'Magento_Checkout/js/action/place-order',
        'Magento_Checkout/js/checkout-data',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Magento_Ui/js/model/messages',
        'Magento_Checkout/js/action/redirect-on-success'
    ],
    function (
        $,
        Component,
        quote,
        url,
        customer,
        _,
        placeOrderAction,
        checkoutData,
        fullScreenLoader,
        additionalValidators,
        Messages,
        redirectOnSuccessAction
    ) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'Alfakher_SlopePayment/payment/slope_payment'
            },
            getMailingAddress: function () {
                return window.checkoutConfig.payment.checkmo.mailingAddress;
            },
            getInstructions: function () {
                return window.checkoutConfig.payment.instructions[this.item.method];
            },
            startSlopeCheckout: function () {
                var self = this;
                this.messageContainer = new Messages();
                var apiUrl = url.build("slope/payment/initiateFlow");
                const endpointUrl = this.apiUrl;

                $.ajax({
                    showLoader: true,
                    url: apiUrl,
                    type: 'GET',
                    dataType: 'json'
                }).done(function (data) {
                    console.log(data);
                    if (typeof data.secret !== 'undefined')
                        var intentSecret = data.secret;
                    self.initializeScope(intentSecret, quote);
                });
            },
            initializeScope: function (intentSecret, quote) {
                var self = this;
                var quote = quote;
                window.initializeSlope({

                    /**
                    * intentSecret is the short-lived token returned by Slope's API when
                    * creating the order. It allows the customer to select their preferred
                    * payment terms and payment method without needing to share your
                    * Slope API keys.
                    */
                    intentSecret: intentSecret,

                    quote: quote,


                    /**
                    * Called when the user has closed the popup. See "Event callbacks page"
                    */
                    onClose: function (payload) {
                        console.log(JSON.stringify(payload));
                    },

                    /**
                    * Called when flow reaches completion. See "Event callbacks page"
                    */
                    onSuccess: function (payload) {
                        console.dir(payload);
                        console.log(quote)
                        self.placeMagentoOrder(quote,payload);
                    },

                    /**
                    * Called when user reaches an terminal error page. See "Event callbacks page"
                    */
                    onFailure: function (payload) {
                        alert(JSON.stringify(payload.errorMessage));
                    },

                    /**
                    * Called when a tracking event occurs. See "Event callbacks page"
                    */
                    onEvent: function (payload) {
                        console.log('event:' + JSON.stringify(payload));
                    }
                });
                window.Slope.open();
            },
            placeMagentoOrder: function (quote,payload) {
                console.log(quote);
                /*this.getData().additional_data.jigar = 'test';*/
                var customData = {
                    'custom_field_1': 'value 1',
                    'custom_field_2': 'value 2'
                };
                //var paymentMethod = quote.paymentMethod();
                //paymentMethod.setAdditionalInformation('custom_data', customData);
                placeOrderAction(this.getData(payload), this.messageContainer)
                    .done(
                        function () {
                            redirectOnSuccessAction.execute();
                        }
                    );
            },

            /**
             * Get payment method data
             */
            getData: function (payload) {
                return {
                    'method': this.item.method,
                    'po_number': null,
                    'additional_data': {
                        'slope_information': JSON.stringify(payload)
                    }
                };
            },

        });
    }
);
