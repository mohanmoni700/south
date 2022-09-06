define([
    'mage/utils/wrapper',
    'Magento_Ui/js/modal/alert',
    'jquery'
], function (wrapper, alert, $) {
    'use strict';

    return function (errorProcessor) {
        errorProcessor.process = wrapper.wrapSuper(errorProcessor.process, function (response, messageContainer) {
            var error = JSON.parse(response.responseText);
            alert({
                title: $.mage.__('Unable to place order'),
                content: $.mage.__(error.message)
            });
            this._super(response, messageContainer);
        });

        return errorProcessor;
    };
});