define(
    [
        'jquery',
        'mage/storage',
        'Magento_Ui/js/modal/alert',
        'Avalara_Excise/js/model/address-model',
        'Avalara_Excise/js/model/url-builder',
        'mage/url',
    ],
    function (
        $,
        storage,
        alert,
        addressModel,
        urlBuilder,
        mageUrl
    ) {
        'use strict';
        return function () {
            var serviceUrl = urlBuilder.createUrl('/carts/billing-validate-address', {}),
                payload = {
                    address: addressModel.originalAddress()
            };

            return $.ajax({
                url: mageUrl.build(serviceUrl),
                type: 'POST',
                data: JSON.stringify(payload),
                async: false,
                global: true,
                contentType: 'application/json'
            }).done(
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
