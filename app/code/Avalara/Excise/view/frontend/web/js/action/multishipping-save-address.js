
define(
    [
        'jquery',
        'mage/storage',
        'Magento_Ui/js/modal/alert',
        'Avalara_Excise/js/model/url-builder',
        'Avalara_Excise/js/model/multishipping-save-address',
    ],
    function (
        $,
        storage,
        alert,
        urlBuilder,
        multishippingSaveAddressService,
    ) {
        'use strict';
        return function (address) {
            var serviceUrl = urlBuilder.createUrl('/multishipping/save-address', {}),

                payload = {
                    address: address,
            };

            return multishippingSaveAddressService(serviceUrl, payload);

        }
    }
);
