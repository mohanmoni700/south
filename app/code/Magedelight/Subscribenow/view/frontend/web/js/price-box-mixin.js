define([
    'jquery',
    'Magento_Catalog/js/price-utils',
    'underscore',
    'mage/template',
    'jquery/ui'
], function ($, utils, _, mageTemplate) {
    'use strict';

    return function (priceBox) {

        $.widget('mage.priceBox', priceBox, {

            /*eslint-disable no-extra-parens*/
            /**
             * Render price unit block.
             */
            reloadPrice: function reDrawPrices()
            {
                var priceFormat = (this.options.priceConfig && this.options.priceConfig.priceFormat) || {},
                    priceTemplate = mageTemplate(this.options.priceTemplate);

                _.each(this.cache.displayPrices, function (price, priceCode) {
                    price.final = _.reduce(price.adjustments, function (memo, amount) {
                        return memo + amount;
                    }, price.amount);

                    var finalPrice = price.final;
                    if (isSubscriptionActive() && finalPrice > 0) {
                        var discountedPrice = getMDSubscriptionDiscount(finalPrice);
                        price.final = finalPrice - discountedPrice;
                    }

                    price.formatted = utils.formatPrice(price.final, priceFormat);

                    $('[data-price-type="' + priceCode + '"]', this.element).html(priceTemplate({
                        data: price
                    }));
                }, this);
            },
        });

        return $.mage.priceBox;

        function isSubscriptionActive()
        {
            var config = getMDSubscriptionConfig();
            if (config) {
                var validType = config.discount_type != 'fixed' ? true : false;
                var productType = config.product_type == 'configurable' ? true : false;

                if ($('[name="options[_1]"]:checked').val() === 'subscription' && validType && productType) {
                    return true;
                } else if (!$.isEmptyObject(config) && validType && config.subscription_type === 'subscription' && productType) {
                    return true;
                }
            }

            return false;
        }

        function getMDSubscriptionDiscount(price)
        {
            var discountedPrice = 0;
            var MDConfig = getMDSubscriptionConfig();
            var discountAmount = MDConfig.discount;

            if (MDConfig.product_type == 'bundle') {
                return discountedPrice;
            }

            if (MDConfig.discount_type == 'fixed') {
                discountedPrice = discountAmount;
            } else {
                var withoutCutomOptionPrice = price - getCustomOptionPrice();
                discountedPrice = withoutCutomOptionPrice * (discountAmount / 100);
            }

            return discountedPrice;
        }

        function getCustomOptionPrice()
        {
            var price = 0;
            jQuery(".product-custom-option:checked").each(function (index, element) {
                var value = jQuery(element).attr('price');
                if (value) {
                    price += parseFloat(value);
                }
            });
            return price;
        }


        function getMDSubscriptionConfig()
        {
            var json = jQuery('#md_subscription_discount_config').val();
            if (json) {
                return JSON.parse(json);
            }
            return null;
        }
    };
});