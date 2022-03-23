define(
    [
        'jquery',
        'Magento_Checkout/js/model/new-customer-address',
        'Magento_Customer/js/customer-data',
        'mage/utils/objects',
        'Avalara_Excise/js/lib/serialize-form'
    ],
    function (
        $,
        address,
        customerData,
        mageUtils
    ) {
        'use strict';
        var countryData = customerData.get('directory-data');

        return {
            /**
             * Convert address form data to Address object
             * @returns {Object}
             * @param formData
             */
            formAddressDataToCustomerAddress: function (formData) {
                // clone address form data to new object
                var addressData = $.extend(true, {}, formData),
                    region,
                    regionName = addressData.region;

                if (mageUtils.isObject(addressData.street)) {
                    addressData.street = this.objectToArray(addressData.street);
                }

                // set region_id for 2.2.11 issue
                if (formData.regionId && typeof addressData.region_id !== undefined) {
                    addressData.region_id = formData.regionId;
                }

                addressData.region = {
                    region_id: addressData.region_id,
                    region: regionName
                };

                if (addressData.region_id
                    && countryData()[addressData.country_id]
                    && countryData()[addressData.country_id]['regions']
                ) {
                    region = countryData()[addressData.country_id]['regions'][addressData.region_id];
                    if (region) {
                        addressData.region.region_id = addressData['region_id'];
                        addressData.region.region = region['name'];
                    }
                }

                var addressAddData = address(addressData);

                if (typeof addressAddData.extension_attributes === 'undefined') {
                    addressAddData.extension_attributes = {};
                }

                if (typeof addressData.extension_attributes !== 'undefined') {
                    addressAddData.extension_attributes.county = addressData.extension_attributes.county;
                } else if (typeof addressData.customAttributes !== 'undefined') {
                    $.each(addressData.customAttributes , function ( key, value ) {
                        if (value.attribute_code == "county") {
                            var extAttr = {"county":value.value};
                            addressAddData.extension_attributes = extAttr;
                        }
                    });
                }

                // Code for compatibility
                if (typeof addressAddData.extension_attributes === 'undefined' && typeof addressData.customAttributes !== 'undefined') {
                    if (typeof addressData.customAttributes.county !== 'undefined') {
                        var extAttr = { "county": addressData.customAttributes.county };
                        addressAddData.extension_attributes = extAttr;
                    }
                }
                // END - Code for compatibility

                if (typeof addressData.county !== 'undefined') {
                    addressAddData.extension_attributes.county = addressData.county;
                }
                return addressAddData;
            }
        };
    }
);
