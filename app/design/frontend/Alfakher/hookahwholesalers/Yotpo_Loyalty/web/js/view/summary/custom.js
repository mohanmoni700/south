define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/quote',
        'Magento_Customer/js/customer-data',
        'underscore'
    ],
    function (Component, quote, customerData, _) {
        'use strict';

        var mixin = {
            defaults: {
                template: 'Yotpo_Loyalty/summary/custom'
            },
            loadJsCustomAfterKoRender: function() {
                var guidId = window.valuesConfig;
                var instanceId = window.swellInstanceId;
                var url = 'https://cdn-widgetsrepository.yotpo.com/v1/loader/' + guidId;
                var script = document.createElement('script');
                script.src = url;
                script.setAttribute('src_type', 'url');
                document.head.appendChild(script);
                jQuery('.yotpo-widget-instance').attr('data-yotpo-instance-id', instanceId);

                // Get current cart quote
                var currentTotals = quote.getTotals();
                if (currentTotals && currentTotals.items) {
                    // Pass item to _isAmastyQuoteItem function
                    var item = currentTotals.items[0]; // Assuming you want to pass the first item in the quote
                    var isAmastyQuoteItem = this._isAmastyQuoteItem(item);
                    console.log('Is Amasty Quote Item:', isAmastyQuoteItem);
                }

                if (isAmastyQuoteItem)
                {
                    jQuery('.parent').hide();
                }
            },

            /**
             * @param {Object} item
             * @return {Boolean}
             * @private
             */
            _isAmastyQuoteItem: function (item) {
                // Implementation of _isAmastyQuoteItem() from the previous code snippet
                var cartData = customerData.get('cart')();
                var quoteItemData = {};
                var isAmastyQuoteItem = false;

                if (!_.isUndefined(cartData.items)) {
                    // Find the item in the cart data that matches the given item's item_id
                    quoteItemData = _.find(cartData.items, function (itemData) {
                        return itemData.item_id == item.item_id;
                    });

                    // If the quoteItemData is found, check if it has the 'is_amasty_quote_item' property
                    if (quoteItemData) {
                        isAmastyQuoteItem = !!quoteItemData['is_amasty_quote_item'];
                    }
                }
                return isAmastyQuoteItem;
            }
        };
        return Component.extend(mixin);
    }
);
