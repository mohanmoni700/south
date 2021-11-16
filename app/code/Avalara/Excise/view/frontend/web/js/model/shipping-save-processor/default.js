
define(
    [
        'jquery',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/resource-url-manager',
        'mage/storage',
        'Magento_Checkout/js/model/payment-service',
        'Magento_Checkout/js/model/payment/method-converter',
        'Magento_Checkout/js/model/error-processor',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Checkout/js/action/select-billing-address',
        'Avalara_Excise/js/view/checkout-validation-handler',
        'Magento_Ui/js/modal/alert'
    ],
    function (
        $,
        quote,
        resourceUrlManager,
        storage,
        paymentService,
        methodConverter,
        errorProcessor,
        fullScreenLoader,
        selectBillingAddressAction,
        checkoutValidationHandler,
        alert
    ) {
        'use strict';

        var payloadExtenderLoaded = false;
        var getPayloadExtender = function () {
            var extenderRequest = new $.Deferred();
            require(
                ['Magento_Checkout/js/model/shipping-save-processor/payload-extender'],
                function (payloadExtender) {
                    extenderRequest.resolve(payloadExtender);
                    // Used for compatibility with Magento versions prior to 2.1.15
                    payloadExtenderLoaded = true;
                },
                function (err) {
                    extenderRequest.reject(err);
                }
            );
            return extenderRequest.promise();
        };

        return function (module) {
            var validateAddressContainerSelector = '#validate_address';
            module.saveShippingInformation = function () {
                var payload;

                if (!quote.billingAddress()) {
                    selectBillingAddressAction(quote.shippingAddress());
                }

                payload = {
                    addressInformation: {
                        shipping_address: quote.shippingAddress(),
                        billing_address: quote.billingAddress(),
                        shipping_method_code: quote.shippingMethod().method_code,
                        shipping_carrier_code: quote.shippingMethod().carrier_code
                    }
                };

                return $.when(getPayloadExtender()).then(function (extender) {
                    extender(payload);
                    return payload;
                }, function (err) {
                    // payloadExtender doesn't exist in 2.1, no problem
                    return $.when(payload);
                }).done(function (pl) {
                    fullScreenLoader.startLoader();

                    return storage.post(
                        resourceUrlManager.getUrlForSetShippingInformation(quote),
                        JSON.stringify(pl)
                    ).done(
                        function (response) {
                            quote.setTotals(response.totals);
                            paymentService.setPaymentMethods(methodConverter(response.payment_methods));
                            // Begin Edit
                            try {
                                checkoutValidationHandler.validationResponseHandler(response);
                            } catch (e) {
                                $(validateAddressContainerSelector + " *").hide();
                            }
                            // End Edit
                            fullScreenLoader.stopLoader(!payloadExtenderLoaded);
                        }
                    ).fail(
                        function (response) {
                            // Begin Edit - Native error message display is not obvious enough, so add to an alert box
                            var messageObject = JSON.parse(response.responseText);
                            alert({
                                title: $.mage.__('Error'),
                                content: messageObject.message
                            });
                            //errorProcessor.process(response);
                            // End Edit
                            fullScreenLoader.stopLoader(!payloadExtenderLoaded);
                        }
                    );
                });
            };
            return module;
        };
    }
);
