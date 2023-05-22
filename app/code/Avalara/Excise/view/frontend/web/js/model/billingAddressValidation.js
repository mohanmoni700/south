
define(
    [
        'jquery',
        'Avalara_Excise/js/view/address-validation-form',
        'Avalara_Excise/js/model/address-model',
        'Avalara_Excise/js/model/address-converter',
        'Avalara_Excise/js/action/set-billing-address',
        'Magento_Checkout/js/model/quote',
        'Avalara_Excise/js/view/checkout-billing-address-validation-modal',
    ],
    function ($, addressValidationForm, addressModel, addressConverter, setBillingAddress, quote, addressValidationModal) {
        'use strict';
        return {
            modal: false,
            options: window.checkoutConfig.billingAddressValidation,
            validationContainer: '.billingValidationModal .modal-content > div',

            validate: function () {
                $('body').trigger('processStart');
                var self = this, isValid;
                if (this.options.validationEnabled &&
                    (typeof this.options.isAddressValid == "undefined" || this.options.isAddressValid === false)
                ) {
                    isValid = self.validateBillingAddress();
                } else {
                    isValid = true;
                }
                $('body').trigger('processStop');
                return isValid;
            },
            validateBillingAddress: function () {
                var isValid = false,
                    self = this,
                    addressObject = addressConverter.formAddressDataToCustomerAddress(quote.billingAddress()),
                    inCountry = $.inArray(addressObject.countryId, self.options.countriesEnabled.split(',')) >= 0;
                addressModel.error(null);
                if (inCountry) {
                    if (!self.modal) {
                        self.modal = addressValidationModal(self.options);
                    }
                    $('.validateAddressForm').show();
                    addressObject = self.cleanUnAddressObject(addressObject);
                    
                    // set region_id for 2.2.11 issue
                    if (addressObject.regionId && typeof addressObject.region_id !== undefined) {
                        addressObject.region_id = addressObject.regionId;
                    }

                    addressModel.originalAddress(addressObject);
                    addressModel.error(null);
                    if (typeof quote.billingAddress().extension_attributes === 'undefined' && typeof addressObject.extension_attributes !== 'undefined') {
                        var newBill = quote.billingAddress();
                        newBill.extension_attributes = addressObject.extension_attributes;
                        quote.billingAddress(newBill);
                    }
                    setBillingAddress().done(function (response) {
                        if (typeof response === 'string') {
                            addressModel.error(response);
                        } else {
                            addressModel.validAddress(response);
                        }
                        addressValidationForm.fillValidateForm(self.validationContainer);
                        if (addressModel.isDifferent() || addressModel.error() != null) {
                            isValid = false;
                            self.modal.openModal();
                            $('.validateAddressForm').show();
                        } else {
                            isValid = true;
                        }
                    });
                    return isValid;
                }
            },
            cleanUnAddressObject: function (address) {
                var allowedAddressProperties = [
                    "customerId",
                    "countryId",
                    "region",
                    "regionId",
                    "regionCode",
                    "street",
                    "company",
                    "telephone",
                    "fax",
                    "postcode",
                    "city",
                    "firstname",
                    "lastname",
                    "middlename",
                    "prefix",
                    "suffix",
                    "vatId",
                    "extension_attributes",
                ];

                var addressKeys = Object.keys(address);
                for (var i = 0; i < addressKeys.length; i++) {
                    if (!allowedAddressProperties.includes(addressKeys[i])) {
                        delete address[addressKeys[i]];
                    }
                }

                return address;
            }

        }
    }
);
