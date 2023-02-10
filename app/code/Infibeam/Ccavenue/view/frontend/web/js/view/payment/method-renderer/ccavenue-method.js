define(
    [
        'Magento_Checkout/js/view/payment/default',
        'Infibeam_Ccavenue/js/action/set-payment-method',
    ],
    function(Component,setPaymentMethod){
    'use strict';

    return Component.extend({
        defaults:{
            'template':'Infibeam_Ccavenue/payment/ccavenue'
        },
        redirectAfterPlaceOrder: false,
        
        afterPlaceOrder: function () {
            setPaymentMethod();    
        }

    });
});
