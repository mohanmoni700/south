define(
    [
        'jquery',
        'Magento_Checkout/js/model/quote',
        'Magento_Customer/js/customer-data',
        'Magento_Customer/js/model/customer',
        'Magento_Checkout/js/model/full-screen-loader',
        'Infibeam_Ccavenue/js/form/form-builder',
        'Magento_Ui/js/modal/alert'
    ],
    function ($, quote, customerData, customer, fullScreenLoader, formBuilder, alert) {
        'use strict';

        return function (messageContainer) {

            var serviceUrl,
                form;

            serviceUrl = window.checkoutConfig.payment.ccavenue.redirectUrl;
            fullScreenLoader.startLoader();
            
            $.ajax({
                url: serviceUrl,
                type: 'post',
                context: this,
                data: {isAjax: 1},
                dataType: 'json',
                success: function (response) {
                    if ($.type(response) === 'object' && !$.isEmptyObject(response)) {
                        $('#ccavenue_payment_form').remove();
                        form = formBuilder.build(
                            {
                                action: response.url,
                                fields: response.fields
                            }
                        );
                        customerData.invalidate(['cart']);
                        
                        if(response.fields['integration_type'] == 'iframe'){
                            $('#iframe_wrapper').fadeIn();
                            $('#iframe_wrapper iframe').load(function() {
                                fullScreenLoader.stopLoader();
                                $('#iframe_wrapper iframe').fadeIn();
                                $('.actions-toolbar').hide();
                            });
                        } else {
                            form.submit();
                        }
                    } else {
                        fullScreenLoader.stopLoader();
                        alert({
                            content: $.mage.__('Sorry, something went wrong. Please try again.')
                        });
                    }
                },
                error: function (response) {
                    fullScreenLoader.stopLoader();
                    alert({
                        content: $.mage.__('Sorry, something went wrong. Please try again later.')
                    });
                }
            });
        };
    }
);


