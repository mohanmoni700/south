define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'paypal',
                component: 'Alfakher_PaymentMethod/js/view/payment/method-renderer/paypal'
            },
            {
                type: 'ach_us_payment',
                component: 'Alfakher_PaymentMethod/js/view/payment/method-renderer/ach_us_payment'
            }
        );
        return Component.extend({});
    }
);