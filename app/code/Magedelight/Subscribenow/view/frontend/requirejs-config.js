var config = {
    map: {
        '*': {
            'MagedelightSubscription': 'Magedelight_Subscribenow/js/subscription',
            'Magento_Paypal/template/payment/payflowpro-form.html':
                'Magedelight_Subscribenow/template/payment/payflowpro-form.html'
        }
    },
    config: {
        mixins: {
            'Magento_Bundle/js/price-bundle': {
                'Magedelight_Subscribenow/js/price-bundle-mixin': true
            },
            'Magento_Catalog/js/price-box': {
                'Magedelight_Subscribenow/js/price-box-mixin': true
            }
        }
    }
};
