
define(
    [
        'jquery',
        'Magento_Checkout/js/model/quote'
    ],
    function (
        $,
        quote
    ) {
        'use strict';

        return function (address, dontCheckForBillingAddress) {
            var quoteShippingAddress = quote.shippingAddress();
            var propertiesToUpdateFromCustomerAddress = ['region', 'region_id', 'country_id', 'street', 'postcode', 'city'];
            var propertiesToUpdateFromQuoteAddress = ['country_id', 'region_code', 'street', 'postcode', 'city', 'region_id', 'region'];
            var propertiesToUpdate = $.extend(propertiesToUpdateFromCustomerAddress, propertiesToUpdateFromQuoteAddress);
            var addressChanges = {};
            for (var index in propertiesToUpdate ) {
                var property = propertiesToUpdate[index];
                if (address.hasOwnProperty(property)) {
                    addressChanges[property] = address[property];
                }
            }
            var newAddress = $.extend(quoteShippingAddress, addressChanges);
            var newCounty = "";
            if (newAddress.extension_attributes !== 'undefined' && address.extension_attributes !== 'undefined') {
                if (newAddress.extension_attributes.county !== 'undefined' && address.extension_attributes.county !== 'undefined') {
                    newAddress.extension_attributes.county = address.extension_attributes.county;
                    newCounty = address.extension_attributes.county;
                }
            }
            if (newAddress.customAttributes !== 'undefined') {
                $.each(newAddress.customAttributes, function (key, attribute) {
                    if (attribute.attribute_code == 'county') {
                        newAddress.customAttributes[key] = { attribute_code: "county", value: newCounty };
                    }
                });
            }
            
            quote.shippingAddress(newAddress);
            
            // dontCheckForBillingAddress allows for the billing address to be updated even when billing address same
            // as shipping is not checked. This is necessary because the checkbox isn't always checked by the time this
            // line is executed. This is only the case on the initial loading of the Review & Payments step so the
            // dontCheckForBillingAddress property is set to true on the initial loading of that step and false when
            // switching between the suggested and original address.
            if ($('input[name=billing-address-same-as-shipping]').filter(':checked').length || dontCheckForBillingAddress) {
                quote.billingAddress(newAddress);
            }
        };
    }
);

