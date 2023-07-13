define([
    'ko',
    'Magedelight_Subscribenow/js/view/checkout/summary/trial_amount',
    'Magento_Checkout/js/model/quote',
    'Magento_Catalog/js/price-utils',
    'Magento_Checkout/js/model/totals'
], function (ko, Component, quote, priceUtils, totals) {
    'use strict';

    var show_hide_trialamount = window.checkoutConfig.show_hide_subscribenow_trial_amount;
    var trial_amount_title = window.checkoutConfig.subscribenow_trial_amount_title;
    var subscribenow_trial_amount = window.checkoutConfig.subscribenow_trial_amount;

    return Component.extend({
        totals: quote.getTotals(),
        canVisibleCustomFeeBlock: show_hide_trialamount,
        getFormattedPrice: ko.observable(priceUtils.formatPrice(subscribenow_trial_amount, quote.getPriceFormat())),
        getTrialFeeTitle: ko.observable(trial_amount_title),
        isDisplayed: function () {
            return this.getValue() != 0;
        },
        getValue: function () {
            var price = 0;
            if (this.totals() && totals.getSegment('subscribenow_trial_amount')) {
                price = totals.getSegment('subscribenow_trial_amount').value;
            }
            return price;
        }
    });
});