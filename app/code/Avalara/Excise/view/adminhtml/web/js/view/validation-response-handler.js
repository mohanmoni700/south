
define(
    [
        'jquery',
        'Magento_Ui/js/modal/alert',
        'Avalara_Excise/js/model/address-model',
        'Avalara_Excise/js/view/address-validation-form-admin'
    ],
    function (
        $,
        alert,
        addressModel,
        addressValidationForm
    ) {
        'use strict';

        return {
            validationResponseHandler: function (response, settings, form) {
                addressModel.error(null);
                if (typeof response !== 'undefined') {
                    if (typeof response === 'string') {
                        addressModel.error(response);
                    } else {
                        addressModel.validAddress(response);
                    }
                    addressValidationForm.fillValidateForm(form, settings);
                    if (addressModel.error() == null && !addressModel.isDifferent()) {
                        alert({
                            title: $.mage.__('Success'),
                            content: $.mage.__('This address is already valid.')
                        });
                    }
                }
            }
        };
    }
);
