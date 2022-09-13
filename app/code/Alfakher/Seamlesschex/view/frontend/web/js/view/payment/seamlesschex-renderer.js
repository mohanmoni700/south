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
                type: 'seamlesschex',
                component: 'Alfakher_Seamlesschex/js/view/payment/method-renderer/seamlesschex'
            }
        );
        return Component.extend({});
    }
);