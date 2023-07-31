define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/quote',
        'Magento_Customer/js/customer-data',
        'underscore',
        'jquery'
    ],
    function (Component, quote, customerData, _,$) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'Yotpo_Loyalty/summary/custom'
            },
            loadJsCustomAfterKoRender: function () {
                var quoteItemData = {};
                var isAmastyQuoteItem = false;
                var guidId = window.valuesConfig;
                var instanceId = window.swellInstanceId;
                var url = 'https://cdn-widgetsrepository.yotpo.com/v1/loader/' + guidId;
                var script = document.createElement('script');
                script.src = url
                script.setAttribute('src_type', 'url')
                document.head.appendChild(script)
                jQuery('.yotpo-widget-instance').attr('data-yotpo-instance-id', instanceId);

                // Get current cart quote
                var quoteData = customerData.get('cart')();
                if (!_.isUndefined(quoteData.items)) {
                    // Find the item in the cart data that matches the given item's item_id
                    if (quoteItemData) {
                        for (var i = 0; i < quoteData.items.length; i++) {
                            if (quoteData.items[i]['is_amasty_quote_item']) {
                                // isAmastyQuoteItem = quoteDataItem[0]['is_amasty_quote_item'];
                                isAmastyQuoteItem = true;
                                break;
                            }
                        }
                        if (isAmastyQuoteItem) {
                            $('.yotpo-widget-instance').hide();
                            $('.yotpo-widget-checkout-redemptions-widget').hide();
                        }
                    }
                }
            }
        });
    }
);
