define(
    [
        'jquery',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/model/quote',
        'mage/url',
        'Magento_Checkout/js/action/place-order',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Magento_Ui/js/model/messages',
        'Magento_Checkout/js/action/redirect-on-success',
        'Magento_Ui/js/modal/alert',
        'mage/translate'
    ],
    function (
        $,
        Component,
        quote,
        url,
        placeOrderAction,
        fullScreenLoader,
        additionalValidators,
        Messages,
        redirectOnSuccessAction,
        alert,
        $t
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

                $.ajax({
                    showLoader: true,
                    url: apiUrl,
                    type: 'GET',
                    dataType: 'json'
                }).done(function (data) {
                    if (typeof data.secret !== 'undefined')
                    {
                        if(data.success === false)
                        {
                            const messages = data.messages;
                            var errorContent = $t("<p><strong>Note: </strong><br>Please correct as per below to place an order</p>");
                            errorContent += "<ul>\n";
                            messages.forEach(function(message) {
                              errorContent += "<li>" + $t(message) + "</li>\n";
                            });
                            errorContent += "</ul>";
                            alert({
                                title: $t('Slope Payment Error'),
                                content: errorContent,
                            });
                            return false;
                        }
                        var intentSecret = data.secret;
                        self.initializeScope(intentSecret, quote);
                    }
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
                        self.placeMagentoOrder(payload);
                    },

                    /**
                    * Called when user reaches an terminal error page. See "Event callbacks page"
                    */
                    onFailure: function (payload) {
                        console.log(JSON.stringify(payload));
                    },

                    /**
                    * Called when a tracking event occurs. See "Event callbacks page"
                    */
                    onEvent: function (payload) {
                        console.log(JSON.stringify(payload));
                    }
                });
                window.Slope.open();
            },
            placeMagentoOrder: function (payload) {
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
