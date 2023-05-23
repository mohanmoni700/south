/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
 define([
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/url-builder',
    'Magento_Customer/js/model/customer',
    'Magento_Checkout/js/model/place-order'
], function (quote, urlBuilder, customer, placeOrderService) {
    'use strict';

    return function (paymentData, messageContainer) {
        var serviceUrl, payload;

        var billingAddress = quote.billingAddress();
        //console.log("magento place order excise billingAddress - 1");
        //console.log(billingAddress);
        //debugger;

        if (billingAddress != undefined) {
            if (billingAddress['extension_attributes'] === undefined) {
                billingAddress['extension_attributes'] = {};
            }
            
            billingAddress['extension_attributes']['county']='';
            if (typeof billingAddress.customAttributes !== 'undefined') {
                $.each(billingAddress.customAttributes , function ( key, value ) {
                    if (value.attribute_code=="county") {
                        billingAddress['extension_attributes']['county'] = value.value;
                    }
                });
            }
        }
        
        //console.log("magento billing address -excise place-order - 2");
        //console.log(billingAddress);
        //debugger;
        
        payload = {
            cartId: quote.getQuoteId(),
            billingAddress: billingAddress,
            paymentMethod: paymentData
        };

        if (customer.isLoggedIn()) {
            serviceUrl = urlBuilder.createUrl('/carts/mine/payment-information', {});
        } else {
            serviceUrl = urlBuilder.createUrl('/guest-carts/:quoteId/payment-information', {
                quoteId: quote.getQuoteId()
            });
            payload.email = quote.guestEmail;
        }

        return placeOrderService(serviceUrl, payload, messageContainer);
    };
});
