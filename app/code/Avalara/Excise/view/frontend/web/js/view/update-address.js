
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
            if (typeof newAddress.extension_attributes !== 'undefined' && typeof address.extension_attributes !== 'undefined') {
                if (typeof newAddress.extension_attributes.county !== 'undefined' && typeof address.extension_attributes.county !== 'undefined') {
                    newAddress.extension_attributes.county = address.extension_attributes.county;
                    newCounty = address.extension_attributes.county;
                }
                // NEW
                if (typeof newAddress.extension_attributes.county === 'undefined' && typeof address.extension_attributes.county !== 'undefined') {
                    newAddress.extension_attributes.county = address.extension_attributes.county;
                    newCounty = address.extension_attributes.county;
                }
                // END - NEW
            }

            if (typeof newAddress.customAttributes !== 'undefined') {
                if (newAddress.customAttributes.length === 0) {
                    newAddress.customAttributes = [{ attribute_code: "county", value: newCounty }];
                }
                var newCustomAttributes = [];
                $.each(newAddress.customAttributes, function (key, attribute) {

                    if (typeof attribute === 'object' && typeof attribute.attribute_code !== 'undefined' && attribute.attribute_code == 'county') {
                        newCustomAttributes.push({ attribute_code: "county", value: newCounty });
                    } else {
                        newCustomAttributes.push({ attribute_code: attribute.attribute_code, value: attribute.value });
                    }
                    /*if (typeof attribute === 'string') {
                        newAddress.customAttributes[key] = newCounty;
                    }*/
                });

                if (newCustomAttributes.length > 0) {
                    newAddress.customAttributes = newCustomAttributes;
                }
            } else if (typeof newAddress.customAttributes === 'undefined') {
                newAddress.customAttributes = [];
                newAddress.customAttributes.push({ attribute_code: "county", value: newCounty });
            }

            if (typeof newAddress.custom_attributes !== 'undefined') {
                $.each(newAddress.custom_attributes, function (key, attribute) {
                    if (attribute.attribute_code == 'county') {
                        newAddress.custom_attributes[key] = { attribute_code: "county", value: newCounty };
                    }
                });
            } else if (typeof newAddress.custom_attributes === 'undefined') {
                newAddress.custom_attributes = [{ attribute_code: "county", value: newCounty }];
            }

            // new code
            quote.shippingAddress(newAddress);
            
            // new code end

            // dontCheckForBillingAddress allows for the billing address to be updated even when billing address same
            // as shipping is not checked. This is necessary because the checkbox isn't always checked by the time this
            // line is executed. This is only the case on the initial loading of the Review & Payments step so the
            // dontCheckForBillingAddress property is set to true on the initial loading of that step and false when
            // switching between the suggested and original address.
            if ($('input[name=billing-address-same-as-shipping]').filter(':checked').length || dontCheckForBillingAddress) {
                quote.billingAddress(newAddress);
                $('input[name=billing-address-same-as-shipping]').trigger('click');
            }
        };
    }
);

