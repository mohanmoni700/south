define([
    'ko',
    'uiComponent',
    'Magento_Checkout/js/model/quote',
    'Magento_Catalog/js/price-utils',
    'Magento_CustomerBalance/js/action/use-balance',
    'mage/url',
    'Magento_Ui/js/model/messageList',
    'mage/translate'
], function (ko, component, quote, priceUtils, useBalanceAction, urlBuilder, messageList, $t) {
    'use strict';

    var amountSubstracted = ko.observable(window.checkoutConfig.payment.customerBalance.amountSubstracted),
        isActive = ko.pureComputed(function () {
            var totals = quote.getTotals();

            return !amountSubstracted() && totals()['grand_total'] > 0;
        });

    return component.extend({
        defaults: {
            template: 'Alfakher_StoreCredit/payment/customer-balance',
            isEnabled: true
        },
        isAvailable: window.checkoutConfig.payment.customerBalance.isAvailable,
        amountSubstracted: window.checkoutConfig.payment.customerBalance.amountSubstracted,
        usedAmount: window.checkoutConfig.payment.customerBalance.usedAmount,
        balance: window.checkoutConfig.payment.customerBalance.balance,

        /** @inheritdoc */
        initObservable: function () {
            this._super()
                .observe('isEnabled');
            return this;
        },

        /**
         * Get active status
         *
         * @return {Boolean}
         */
        isActive: function () {
            return isActive();
        },

        /**
         * Format customer balance
         *
         * @return {String}
         */
        formatBalance: function () {
            return priceUtils.formatPrice(this.balance, quote.getPriceFormat());
        },

        /**
         * Set amount substracted from checkout.
         *
         * @param {Boolean} isAmountSubstracted
         * @return {Object}
         */
        setAmountSubstracted: function (isAmountSubstracted) {
            amountSubstracted(isAmountSubstracted);

            return this;
        },

        /**
         * Send request to use balance
         */
        sendRequest: function () {
            messageList.clear();
            var message = $t('Entered Amount is invalid');
            var totalmessage = $t('Please enter the value less than Grand Total')
            var validationMessage = $t('Please add the partial amount.');
            var amount = jQuery('#partial_storecredit').val();
            var storeCreditType = jQuery('#storecredit-type').val();
            if (storeCreditType == 'partial') {
                if (amount == '') {
                    messageList.addErrorMessage({
                        'message': validationMessage
                    });
                    return false;
                }
            }
            jQuery.ajax({
                url: urlBuilder.build("partialstorecredit/index/addamount"),
                type: "POST",
                data: {storeCreditAmount:amount,storeCreditType:storeCreditType},
                showLoader: true,
                cache: false,
                success: function(response){
                    if (response.success == true) {
                        amountSubstracted(true);
                        useBalanceAction();
                    } else {
                        messageList.addErrorMessage({
                            'message': message
                        });
                    }
                    if(parseInt(response.applied) > parseInt(response.total)){
                        messageList.addErrorMessage({
                            'message': totalmessage
                        });
                    }
                }
            });
        },

        selectStoreCredit: function (event) {
            if (event == 'use-partial-storecredit') {
                jQuery("#partial-amount").css("display", "block");
                jQuery("#partial-amount").addClass("active");
                jQuery('#storecredit-type').val("partial");
                jQuery('#use-partial-storecredit').addClass("active");
                jQuery("#use-all-storecredit").removeClass("active");
            } else {
                jQuery("#partial-amount").css("display", "none");
                jQuery('#storecredit-type').val("all");
                jQuery("#partial-amount").removeClass("active");
                jQuery('#use-all-storecredit').addClass("active");
                jQuery("#use-partial-storecredit").removeClass("active");
            }
            return this;
        }
    });
});
