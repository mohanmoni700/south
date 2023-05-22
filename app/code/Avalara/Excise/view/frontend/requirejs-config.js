var config = {
    map: {
        '*': {
            addressValidation: 'Avalara_Excise/js/addressValidation',
            multiShippingAddressValidation: 'Avalara_Excise/js/multishipping-address-validation',
            'Magento_Checkout/template/shipping-address/address-renderer/default.html': 'Avalara_Excise/template/shipping-address/address-renderer/default.html'
        }
    },
    config: {
        mixins: {
            'Magento_Checkout/js/model/step-navigator': {
                'Avalara_Excise/js/model/step-navigator/mixin': true
            },
            'Magento_Checkout/js/model/shipping-save-processor/default': {
                'Avalara_Excise/js/model/shipping-save-processor/default': true
            },

            'Magento_Checkout/js/action/set-billing-address': {
                'Avalara_Excise/js/action/set-billing-address-mixin': true
            },
            'Magento_Checkout/js/action/set-shipping-information': {
                'Avalara_Excise/js/action/set-shipping-information-mixin': true
            },
            'Magento_Checkout/js/action/create-shipping-address': {
                'Avalara_Excise/js/action/create-shipping-address-mixin': true
            },
            'Magento_Checkout/js/view/billing-address': {
                'Avalara_Excise/js/view/billing-address-mixin': true
            },

        }
    }
};
