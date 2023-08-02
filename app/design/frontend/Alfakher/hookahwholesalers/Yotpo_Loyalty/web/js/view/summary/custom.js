define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/quote',
        'Magento_Customer/js/customer-data',
        'underscore',
        'jquery',
        'domReady'
    ],
    function (Component, quote, customerData, _,$,domReady) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'Yotpo_Loyalty/summary/custom'
            },
            loadJsCustomAfterKoRender: function () {
                var guidId = window.valuesConfig;
                var instanceId = window.swellInstanceId;
                var url = 'https://cdn-widgetsrepository.yotpo.com/v1/loader/' + guidId;
                var script = document.createElement('script');
                script.src = url
                script.setAttribute('src_type', 'url')
                document.head.appendChild(script)
                jQuery('.yotpo-widget-instance').attr('data-yotpo-instance-id', instanceId);

                // Get current cart quote
                domReady(function () {
                    let isAmastyQuoteItem = false;
                    let quoteData = customerData.get('cart')();
                    let cartItems = quoteData.items || [];
                    // Find the item in the cart data that matches the given item's is_amasty_quote_item flag
                    _.some(cartItems, function (item) {
                        if (item.is_amasty_quote_item) {
                            isAmastyQuoteItem = true;
                            return true;
                        }
                        return false;
                    });
                    customerData.set('is_amasty_quote_item', isAmastyQuoteItem);
                });
            }
        });
    }
);
