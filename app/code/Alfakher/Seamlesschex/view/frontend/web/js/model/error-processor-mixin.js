define([
    'mage/utils/wrapper'
], function (wrapper) {
    'use strict';

    return function (errorProcessor) {
        errorProcessor.process = wrapper.wrapSuper(errorProcessor.process, function (response, messageContainer) {
           
            var error = JSON.parse(response.responseText);
            if(error.message == "Your ACH payment request has been decline, please try with another ACH details or choose another payment method."){
                showAlert();
            }
            this._super(response, messageContainer);
        });

        return errorProcessor;
    };
});
function showAlert(){
    require([
    'Magento_Ui/js/modal/alert',
    'jquery'
    ], function(alert, $) {

    alert({
        title: $.mage.__('Unable to place order'),
        content: $.mage.__('Your ACH payment request has been decline, please try with another ACH details or choose another payment method.')
    });

});
}