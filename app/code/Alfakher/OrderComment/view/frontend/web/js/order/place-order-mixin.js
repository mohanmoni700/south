define([
    'jquery',
    'mage/utils/wrapper',
    'Alfakher_OrderComment/js/order/ordercomment-assigner'
], function ($, wrapper, ordercommentAssigner) {
    'use strict';
    return function (placeOrderAction) {
        /** Override default place order action and add comments to request */
        return wrapper.wrap(placeOrderAction, function (originalAction, paymentData, messageContainer) {
            ordercommentAssigner(paymentData);
            return originalAction(paymentData, messageContainer);
        });
    };
});