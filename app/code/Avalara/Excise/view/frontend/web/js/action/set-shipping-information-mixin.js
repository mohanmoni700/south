/*jshint browser:true jquery:true*/
/*global alert*/
define([
    'jquery',
    'mage/utils/wrapper',
    'Magento_Checkout/js/model/quote'
], function ($, wrapper, quote) {
    'use strict';

    return function (setShippingInformationAction) {

        return wrapper.wrap(setShippingInformationAction, function (originalAction) {

            var shippingAddress = quote.shippingAddress();
            if ( shippingAddress != undefined) {
                /*if (typeof shippingAddress['extension_attributes'] === undefined) {
                    shippingAddress['extension_attributes'] = {};
                }

                if (shippingAddress.customAttributes != undefined) {
                    $.each(shippingAddress.customAttributes , function(key, attribute) {
                        shippingAddress['extension_attributes'][attribute.attribute_code] = attribute.value;
                    });
                }*/
                if (typeof shippingAddress.extension_attributes === 'undefined') {
                    shippingAddress.extension_attributes = {};
                }

                if (typeof shippingAddress.customAttributes != 'undefined') {
                    $.each(shippingAddress.customAttributes, function (key, attribute) {
                        if (typeof attribute === 'object') {
                            shippingAddress.extension_attributes[attribute.attribute_code] = attribute.value;
                        } else if (typeof attribute === 'string') {
                            shippingAddress.extension_attributes[key] = attribute;
                        }
                    });
                }
                quote.shippingAddress(shippingAddress);
            }
            return originalAction();
        });
    };
});


