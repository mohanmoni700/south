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
                type: 'slope_payment',
                component: 'Alfakher_SlopePayment/js/view/payment/method-renderer/slope_payment-method'
            }
        );
        return Component.extend({});
    }
);