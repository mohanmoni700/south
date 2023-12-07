define([
    'jquery',
    'Magento_Ui/js/modal/confirm'
], function ($, modal) {
    'use strict';

    return function () {
        $(document).on('click', '[name="update_cart_action"][value="update_qty"]', function (event) {
            event.preventDefault();

            modal({
                content: $.mage.__('Are you sure you want to update the quote?'),
                actions: {
                    confirm: function () {
                        $('#form-validate').submit();
                        $('.page.messages').show();
                    }
                }
            });
        });
    };
});
