define([
    'ko',
    'Magedelight_Subscribenow/js/view/checkout/summary/init_amount',
    'Magento_Checkout/js/model/quote',
    'Magento_Catalog/js/price-utils',
    'Magento_Checkout/js/model/totals'
], function (ko, Component, quote, priceUtils, totals) {
    'use strict';

    var show_hide_initamount = window.checkoutConfig.show_hide_subscribenow_init_amount;
    var init_amount_title = window.checkoutConfig.subscribenow_init_amount_title;
    var subscribenow_init_amount = window.checkoutConfig.subscribenow_init_amount;

    return Component.extend({
        totals: quote.getTotals(),
        canVisibleCustomFeeBlock: show_hide_initamount,
        getFormattedPrice: ko.observable(priceUtils.formatPrice(subscribenow_init_amount, quote.getPriceFormat())),
        getInitFeeTitle: ko.observable(init_amount_title),
        isDisplayed: function () {
            return this.getValue() != 0;
        },
        getValue: function () {
            var price = 0;
            if (this.totals() && totals.getSegment('subscribenow_init_amount')) {
                price = totals.getSegment('subscribenow_init_amount').value;
            }
            return price;
        }
    });
});