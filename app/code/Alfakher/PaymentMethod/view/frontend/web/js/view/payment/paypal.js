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
                type: 'offline_paypal',
                component: 'Alfakher_PaymentMethod/js/view/payment/method-renderer/offline_paypal'
            },
            {
                type: 'ach_us_payment',
                component: 'Alfakher_PaymentMethod/js/view/payment/method-renderer/ach_us_payment'
            },
            {
                type: 'zelle',
                component: 'Alfakher_PaymentMethod/js/view/payment/method-renderer/zelle'
            }
        );
        return Component.extend({});
    }
);