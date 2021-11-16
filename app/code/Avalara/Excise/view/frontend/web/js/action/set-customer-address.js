define(
    [
        'jquery',
        'mage/storage',
        'Magento_Ui/js/modal/alert',
        'Avalara_Excise/js/model/address-model',
        'Avalara_Excise/js/model/url-builder'
    ],
    function (
        $,
        storage,
        alert,
        addressModel,
        urlBuilder
    ) {
        'use strict';
        return function () {
            var serviceUrl;
            var payload = {
                address: addressModel.originalAddress()
            };

            serviceUrl = urlBuilder.createUrl('/avalara/address/validate', {});

            return storage.post(
                serviceUrl,
                JSON.stringify(payload)
            ).done(
                function (response) {
                    return response;
                }
            ).fail(
                function (response) {
                    var messageObject = JSON.parse(response.responseText);
                    alert({
                        title: $.mage.__('Error'),
                        content: messageObject.message
                    });
                }
            );
        }
    }
);
