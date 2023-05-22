
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Avalara_Excise/js/model/billingAddressValidation'
    ],
    function (Component, additionalValidators, billingAddressValidator) {
        'use strict';
        additionalValidators.registerValidator(billingAddressValidator);
        return Component.extend({});
    }
);
