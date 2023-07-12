define(
    ["jquery","priceUtils", "mage/calendar", "mage/translate"],
    function ($,priceUtils, calendar) {
        "use strict";
        var config = {
            'content': '#md_subscription_content',
            'susbcriptioOption': 'input:radio[name="options[_1]"]',
            'groupPriceSelector': 'div[data-role="priceBox"] .price-container .price-wrapper',
            'discountConfigElement': '#md_subscription_discount_config',
            'groupPrices': {'oldPrice':{},'newPrice':{}},
            'subscriptionStartDate': '#md_subscription_start',
            'subscriptionEndDate': '#md_subscription_end',
            'endTypeOptions': 'input:radio[name="end_type"]'
        };

        return {

            init: function () {
                var self = this;
                var optionValue = self.getOptionValue();

                if (self.hasSubscription(optionValue)) {
                    self.showHideBlock(optionValue)
                }
                /*If Either Hide Message*/
                if($('#no_subscription').val()){
                    $('.subscription-message').hide();
                }else{
                    if(!isAllowedCustomer){
                        $(".box-tocart").hide();
                    }
                }
                $(config.susbcriptioOption).on('change', function (event) {
                    if (event.originalEvent !== undefined) {
                        var isSubscription = self.hasSubscription($(this).val());
                        /*Remove Add To cart for not allowed customer (Either Sunscription)*/
                        if(!isAllowedCustomer){
                            if(isSubscription){
                                $('.subscription-message').show();
                                $(".box-tocart").hide();
                            }else{
                                $('.subscription-message').hide();
                                $(".box-tocart").show();
                            }
                        }
                        self.showHideBlock(isSubscription);
                    }
                });
                $(config.endTypeOptions).each(function() {
                    if ($(this).is(":checked")) {
                        $(this).siblings('.end_type_content').show();
                    }
                });
                $(config.endTypeOptions).on('change', function (event) {
                    if(event.originalEvent !== undefined){
                        $('.end_type_content').hide();
                        $(this).siblings('.end_type_content').show();
                    }
                });
            },

            initDatePicker: function (opt) {
                var year = (new Date).getFullYear();
                var priceFormat = {
                    decimalSymbol: '.',
                    groupLength: 3,
                    groupSymbol: ",",
                    integerRequired: false,
                    precision: 2,
                    requiredPrecision: 2
                }; 
                $(config.subscriptionStartDate).calendar({
                    dateFormat: "dd-mm-yy",
                    singleClick : true,
                    minDate: 0,
                    showButtonPanel: true,
                    changeMonth: true,
                    changeYear: true,
                    showOn: "both",
                    onSelect: function () {
                        var thisDate = $(this).val();
                        var currentDate = new Date();
                        var selectedDate = new Date(thisDate);
                        var initialFee = opt.initialFee;
                        if(selectedDate > currentDate) {
                            var note = $.mage.__('Actual order will be placed on %1, as of now order will be placed with Zero amount to create subscription profile').replace('%1',thisDate);
                            $('.subscription-field .note').html(note);
                            if(initialFee == 0) {
                                if ($('#md_subscription_content div.feemsg').length) {
                                    $(".feemsg").remove();
                                }
                                $('#md_subscription_content').append('<div class="feemsg">We will charge ' + priceUtils.formatPrice(1, priceFormat) + ' to save your card token if you choose a future subscription date.</div>');
                            }
                        } else {
                            $('.subscription-field .note').html("");
                        }
                    }
                });
                $(config.subscriptionEndDate).calendar({
                    dateFormat: "dd-mm-yy",
                    singleClick : true,
                    minDate: 0,
                    showButtonPanel: true,
                    changeMonth: true,
                    changeYear: true,
                    showOn: "both",
                });
            },

            hasSubscription: function (value) {
                if (value) {
                    return value.toString() === "subscription";
                }
                return false;
            },

            getOptionValue: function () {
                return $(config.susbcriptioOption + ':checked').val();
            },

            showHideBlock: function (show) {
                if (this.isGroupProduct()) {
                    this.changeGroupProductPrice(show);
                }

                if (show) {
                    $(config.content).show();
                } else {
                    $(config.content).hide();
                }
            },

            discountConfig: function () {
                var json = $(config.discountConfigElement).val();
                if (json) {
                    return JSON.parse(json);
                }
                return {};
            },

            isGroupProduct: function () {
                var config = this.discountConfig();
                return (config && config.product_type == 'grouped');
            },

            getCurrency: function () {
                return this.discountConfig().currency;
            },

            changeGroupProductPrice: function (show) {
                var self = this;
                $(config.groupPriceSelector).each(function (k, element) {
                    var id = "#" + element.id;
                    var price = $(id).data('price-amount');
                    var discountedPrice = self.calculateDiscount(price);
                    var currency = self.getCurrency();

                    if (!config.groupPrices.oldPrice[id]) {
                        config.groupPrices.oldPrice[id] = price;
                    }

                    if (!config.groupPrices.newPrice[id]) {
                        config.groupPrices.newPrice[id] = discountedPrice;
                    }
                    var currentNewPrice = config.groupPrices.newPrice[id];
                    if (!show) {
                        currentNewPrice = config.groupPrices.oldPrice[id];
                    }

                    $(id).attr('data-price-amount', currentNewPrice);
                    $(id + ' span').html(priceUtils.formatPrice(currentNewPrice, currency));
                });
            },

            calculateDiscount: function (price) {
                if (!price) {
                    return 0;
                }

                var discountType = this.discountConfig().discount_type;
                var discountAmount = this.discountConfig().discount;
                if (discountAmount != this.discountConfig().discount_locale) {
                    discountAmount = this.discountConfig().discount_locale;
                }

                var discount = (discountType == 'fixed') ? discountAmount : (price * (discountAmount / 100));
                if (discount > price) {
                    discount = price;
                }

                return parseFloat(price) - parseFloat(discount);
            }
        }
    }
);
