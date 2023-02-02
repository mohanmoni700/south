define([
    'jquery',
    'Magento_Customer/js/customer-data'
    ], function($,customerData){
        "use strict";
        function main(config) {
            var ajaxUrl = config.ajaxUrl;
            $(document).on('click','.remove_outof_stock',function() {
                $.ajax({
                    showLoader: true,
                    url: ajaxUrl,
                    type: "POST",
                    success: function (data) {
                        if(data.message == 'success') {
                            $('.checkout-cart-index .message-error').hide();
                            customerData.set('messages', {
                                messages: [{
                                    text: data.value,
                                    type: 'success'
                                }]
                            });
                            window.location.reload();
                        }
                    }
                });
            });
        };
    return main;
});