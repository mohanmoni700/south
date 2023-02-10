/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
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
                type: 'vrpayecommerce_ccsaved',
                component: 'Vrpayecommerce_Vrpayecommerce/js/view/payment/method-renderer/vrpayecommerce-method'
            },
            {
                type: 'vrpayecommerce_creditcard',
                component: 'Vrpayecommerce_Vrpayecommerce/js/view/payment/method-renderer/vrpayecommerce-method'
            },
            {
                type: 'vrpayecommerce_ddsaved',
                component: 'Vrpayecommerce_Vrpayecommerce/js/view/payment/method-renderer/vrpayecommerce-method'
            },
            {
                type: 'vrpayecommerce_directdebit',
                component: 'Vrpayecommerce_Vrpayecommerce/js/view/payment/method-renderer/vrpayecommerce-method'
            },
            {
                type: 'vrpayecommerce_giropay',
                component: 'Vrpayecommerce_Vrpayecommerce/js/view/payment/method-renderer/vrpayecommerce-method'
            },
            {
                type: 'vrpayecommerce_klarnasliceit',
                component: 'Vrpayecommerce_Vrpayecommerce/js/view/payment/method-renderer/vrpayecommerce-method'
            },
            {
                type: 'vrpayecommerce_klarnapaylater',
                component: 'Vrpayecommerce_Vrpayecommerce/js/view/payment/method-renderer/vrpayecommerce-method'
            },
            {
                type: 'vrpayecommerce_paydirekt',
                component: 'Vrpayecommerce_Vrpayecommerce/js/view/payment/method-renderer/vrpayecommerce-method'
            },
            {
                type: 'vrpayecommerce_paypalsaved',
                component: 'Vrpayecommerce_Vrpayecommerce/js/view/payment/method-renderer/vrpayecommerce-method'
            },
            {
                type: 'vrpayecommerce_paypal',
                component: 'Vrpayecommerce_Vrpayecommerce/js/view/payment/method-renderer/vrpayecommerce-method'
            },
            {
                type: 'vrpayecommerce_klarnaobt',
                component: 'Vrpayecommerce_Vrpayecommerce/js/view/payment/method-renderer/vrpayecommerce-method'
            },
            {
                type: 'vrpayecommerce_easycredit',
                component: 'Vrpayecommerce_Vrpayecommerce/js/view/payment/method-renderer/vrpayecommerce-method'
            },
            {
                type: 'vrpayecommerce_enterpay',
                component: 'Vrpayecommerce_Vrpayecommerce/js/view/payment/method-renderer/vrpayecommerce-method'
            }
        );
        /** Add view logic here if needed */
        return Component.extend({});
    }
);