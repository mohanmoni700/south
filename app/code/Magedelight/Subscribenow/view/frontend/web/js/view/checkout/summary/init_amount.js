define([
    'Magento_Checkout/js/view/summary/abstract-total',
    'Magento_Checkout/js/model/quote',
    'Magento_Catalog/js/price-utils',
    'Magento_Checkout/js/model/totals'
], function (Component, quote, priceUtils, totals) {
        "use strict";
        return Component.extend({
            defaults: {
                isFullTaxSummaryDisplayed: window.checkoutConfig.isFullTaxSummaryDisplayed || false,
                template: 'Magedelight_Subscribenow/checkout/summary/init_amount'
            },
            totals: quote.getTotals(),
            isTaxDisplayedInGrandTotal: window.checkoutConfig.includeTaxInGrandTotal || false,
            isDisplayed: function () {
                return this.isFullMode();
            },
            getValue: function () {
                var price = 0;
                if (this.totals()) {
                    price = totals.getSegment('subscribenow_init_amount').value;
                }
                return this.getFormattedPrice(price);
            },
            getBaseValue: function () {
                var price = 0;
                if (this.totals()) {
                    price = this.totals().base_subscribenow_init_amount;
                }
                return priceUtils.formatPrice(price, quote.getBasePriceFormat());
            }
        });
});