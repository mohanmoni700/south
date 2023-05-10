define([
    'jquery',
    'underscore',
    'mage/template',
    'priceUtils',
    'priceBox'
], function ($, _, mageTemplate, utils) {
    'use strict';

    return function (priceBundle) {
        var oldPrices = {};

        // On subscribenow change
        $('[name="options[_1]"]').change(function () {
            var options = 'input.bundle.option, select.bundle.option, textarea.bundle.option';
            $(options).trigger('change');
        });

        $.widget('mage.priceBundle', priceBundle, {

            /**
             * Handle change on bundle option inputs
             * @param {jQuery.Event} event
             * @private
             */
            _onBundleOptionChanged: function onBundleOptionChanged(event)
            {
                var changes,
                    bundleOption = $(event.target),
                    priceBox = $(this.options.priceBoxSelector, this.element),
                    handler = this.options.optionHandlers[bundleOption.data('role')];

                bundleOption.data('optionContainer', bundleOption.closest(this.options.controlContainer));
                bundleOption.data('qtyField', bundleOption.data('optionContainer').find(this.options.qtyFieldSelector));

                if (handler && handler instanceof Function ) {
                    changes = handler(
                        bundleOption,
                        this.options.optionConfig,
                        this
                    );
                } else {
                    changes = defaultGetOptionValue(bundleOption, this.options.optionConfig);//eslint-disable-line
                }

                if (changes) {
                    priceBox.trigger('updatePrice', changes);
                }
                this.updateProductSummary();
            },

        });

        return $.mage.priceBundle;

        /**
         * Converts option value to priceBox object
         *
         * @param   {jQuery} element
         * @param   {Object} config
         * @returns {Object|null} - priceBox object with additional prices
         */
        function defaultGetOptionValue(element, config)
        {
            updatePriceTitle(config.options);

            var changes = {},
                optionHash,
                tempChanges,
                qtyField,
                optionId = utils.findOptionId(element[0]),
                optionValue = element.val() || null,
                optionName = element.prop('name'),
                optionType = element.prop('type'),
                optionConfig = config.options[optionId].selections,
                optionQty = 0,
                canQtyCustomize = false,
                selectedIds = config.selected;

            switch (optionType) {
                case 'radio':
                case 'select-one':

                    if (optionType === 'radio' && !element.is(':checked')) {
                        return null;
                    }

                    qtyField = element.data('qtyField');
                    qtyField.data('option', element);

                    if (optionValue) {
                        optionQty = optionConfig[optionValue].qty || 0;
                        canQtyCustomize = optionConfig[optionValue].customQty === '1';
                        toggleQtyField(qtyField, optionQty, optionId, optionValue, canQtyCustomize);//eslint-disable-line
                        tempChanges = utils.deepClone(optionConfig[optionValue].prices);
                        tempChanges = applyTierPrice(//eslint-disable-line
                            tempChanges,
                            optionQty,
                            optionConfig[optionValue]
                        );
                        tempChanges = applyQty(tempChanges, optionQty);//eslint-disable-line
                    } else {
                        tempChanges = {};
                        toggleQtyField(qtyField, '0', optionId, optionValue, false);//eslint-disable-line
                    }
                    optionHash = 'bundle-option-' + optionName;
                    changes[optionHash] = tempChanges;
                    selectedIds[optionId] = [optionValue];
                    break;

                case 'select-multiple':
                    optionValue = _.compact(optionValue);

                    _.each(optionConfig, function (row, optionValueCode) {
                        optionHash = 'bundle-option-' + optionName + '##' + optionValueCode;
                        optionQty = row.qty || 0;
                        tempChanges = utils.deepClone(row.prices);
                        tempChanges = applyTierPrice(tempChanges, optionQty, optionConfig);//eslint-disable-line
                        tempChanges = applyQty(tempChanges, optionQty);//eslint-disable-line
                        changes[optionHash] = _.contains(optionValue, optionValueCode) ? tempChanges : {};

                        if (getMDSubscriptionConfig() && getMDSubscriptionConfig().subscription_type == 'either' && $("#subscribe_this_product").is(':checked')) {
                            getSubscribenowDiscount(changes[optionHash], optionQty);
                        }
                    });

                    selectedIds[optionId] = optionValue || [];
                    break;

                case 'checkbox':
                    optionHash = 'bundle-option-' + optionName + '##' + optionValue;
                    optionQty = optionConfig[optionValue].qty || 0;
                    tempChanges = utils.deepClone(optionConfig[optionValue].prices);
                    tempChanges = applyTierPrice(tempChanges, optionQty, optionConfig);//eslint-disable-line
                    tempChanges = applyQty(tempChanges, optionQty);//eslint-disable-line
                    changes[optionHash] = element.is(':checked') ? tempChanges : {};

                    selectedIds[optionId] = selectedIds[optionId] || [];

                    if (!_.contains(selectedIds[optionId], optionValue) && element.is(':checked')) {
                        selectedIds[optionId].push(optionValue);
                    } else if (!element.is(':checked')) {
                        selectedIds[optionId] = _.without(selectedIds[optionId], optionValue);
                    }
                    break;

                case 'hidden':
                    optionHash = 'bundle-option-' + optionName + '##' + optionValue;
                    optionQty = optionConfig[optionValue].qty || 0;
                    canQtyCustomize = optionConfig[optionValue].customQty === '1';
                    qtyField = element.data('qtyField');
                    qtyField.data('option', element);
                    toggleQtyField(qtyField, optionQty, optionId, optionValue, canQtyCustomize);//eslint-disable-line
                    tempChanges = utils.deepClone(optionConfig[optionValue].prices);
                    tempChanges = applyTierPrice(tempChanges, optionQty, optionConfig);//eslint-disable-line
                    tempChanges = applyQty(tempChanges, optionQty);//eslint-disable-line

                    optionHash = 'bundle-option-' + optionName;
                    changes[optionHash] = tempChanges;
                    selectedIds[optionId] = [optionValue];
                    break;
            }

            if (getMDSubscriptionConfig()
                && getMDSubscriptionConfig().subscription_type == 'either'
                && $("#subscribe_this_product").is(':checked')
                && optionType != 'select-multiple'
            ) {
                getSubscribenowDiscount(changes[optionHash], optionQty);
            }

            return changes;
        }

        function updatePriceTitle(options)
        {
            if (options &&
                getMDSubscriptionConfig() &&
                getMDSubscriptionConfig().subscription_type == 'either'
            ) {
                $.each(options, function ( key, value ) {
                    if ('selections' in value && value.selections) {
                        $.each(value.selections, function ( k, v ) {
                            var control = '#bundle-option-'+ key + '-' + k;
                            var discountPrice = v.prices.finalPrice.amount;

                            if ($("#subscribe_this_product").is(':checked')) {
                                var productprice = discountPrice * parseFloat(v.qty);
                                discountPrice = getMDSubscriptionDiscount(productprice, parseFloat(v.qty));
                            }

                            if (value.isMulti) {
                                control = $('#bundle-option-'+ key + ' option[value="'+k+'"]');
                                if (control.length) {
                                    updateMultiSelectPrice(control,discountPrice);
                                } else {
                                    control = '#bundle-option-'+ key + '-' + k;
                                    updateInputPrice(control,discountPrice);
                                }
                            } else {
                                updateInputPrice(control,discountPrice);
                            }
                        });
                    }
                });
            }
        }

        function updateMultiSelectPrice(control, discountPrice)
        {
            if (control && control.length) {
                var optionText = control.text();
                if (optionText) {
                    var splitedText = optionText.split('+');
                    if (splitedText) {
                        if (splitedText[1] != discountPrice) {
                            control.text(splitedText[0] + '+ ' + utils.formatPrice(discountPrice));
                        }
                    }
                }
            }
        }

        function updateInputPrice(control, discountPrice)
        {
            if (control && control.length) {
                var currentPrice = $(control).parent().find('.price-container').find('.price-wrapper')
                    .attr('data-price-amount');

                if (currentPrice != discountPrice) {
                    $(control).parent().find('.price-container').find('.price-wrapper')
                        .attr('data-price-amount', discountPrice)
                        .find('span.price')
                        .html(utils.formatPrice(discountPrice));
                }
            }
        }

        function getSubscribenowDiscount(changes, optionQty)
        {
            if ($.isEmptyObject(changes)) {
                return changes;
            }

            var CurrentPrice = changes.basePrice.amount;
            var FinalPrice = changes.finalPrice.amount;

            var CurrentDiscountedPrice = getMDSubscriptionDiscount(CurrentPrice, optionQty);
            var FinalDiscountedPrice = getMDSubscriptionDiscount(FinalPrice, optionQty);

            changes.basePrice.amount = CurrentDiscountedPrice;
            changes.finalPrice.amount = FinalDiscountedPrice;

            return changes;
        }

        function getMDSubscriptionDiscount(price, qty)
        {
            var discountedPrice = 0, result = 0, MDConfig = getMDSubscriptionConfig();

            if (!price) {
                return result;
            }

            var discountAmount = MDConfig.discount;
            if (MDConfig.discount_type == 'fixed') {
                discountedPrice = discountAmount;
            } else {
                discountedPrice = price * (discountAmount / 100);
            }

            var singleItemPrice = price;
            if (MDConfig.discount_type == 'fixed' && price) {
                singleItemPrice = (price / qty);
                var currentPrice = singleItemPrice || 0;
                if (discountedPrice > currentPrice) {
                    return result;
                }
            }

            if (MDConfig.discount_type == 'fixed'  && qty > 1) {
                var singleItemDiscountedPrice = Math.abs(singleItemPrice - discountedPrice);

                if (singleItemDiscountedPrice) {
                    result = (parseFloat(singleItemDiscountedPrice) * parseFloat(qty));
                }
            } else {
                result = Math.abs(price - discountedPrice);
            }

            return result;
        }

        function getMDSubscriptionConfig()
        {
            var json = jQuery('#md_subscription_discount_config').val();
            if (json) {
                return JSON.parse(json);
            }
            return null;
        }

        /**
         * Helper to toggle qty field
         * @param {jQuery} element
         * @param {String|Number} value
         * @param {String|Number} optionId
         * @param {String|Number} optionValueId
         * @param {Boolean} canEdit
         */
        function toggleQtyField(element, value, optionId, optionValueId, canEdit)
        {
            element
                .val(value)
                .data('optionId', optionId)
                .data('optionValueId', optionValueId)
                .attr('disabled', !canEdit);

            if (canEdit) {
                element.removeClass('qty-disabled');
            } else {
                element.addClass('qty-disabled');
            }
        }

        /**
         * Helper to multiply on qty
         *
         * @param   {Object} prices
         * @param   {Number} qty
         * @returns {Object}
         */
        function applyQty(prices, qty)
        {
            _.each(prices, function (everyPrice) {
                everyPrice.amount *= qty;
                _.each(everyPrice.adjustments, function (el, index) {
                    everyPrice.adjustments[index] *= qty;
                });
            });

            return prices;
        }

        /**
         * Helper to limit price with tier price
         *
         * @param {Object} oneItemPrice
         * @param {Number} qty
         * @param {Object} optionConfig
         * @returns {Object}
         */
        function applyTierPrice(oneItemPrice, qty, optionConfig)
        {
            var tiers = optionConfig.tierPrice,
                magicKey = _.keys(oneItemPrice)[0],
                lowest = false;

            _.each(tiers, function (tier, index) {
                if (tier['price_qty'] > qty) {
                    return;
                }

                if (tier.prices[magicKey].amount < oneItemPrice[magicKey].amount) {
                    lowest = index;
                }
            });

            if (lowest !== false) {
                oneItemPrice = utils.deepClone(tiers[lowest].prices);
            }

            return oneItemPrice;
        }
    };
});