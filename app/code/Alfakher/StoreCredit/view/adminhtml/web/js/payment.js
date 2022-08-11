define([
    'jquery',
    'uiComponent',
    'mage/translate'
], function ($, Class, $t) {
    'use strict';

    return Class.extend({
        defaults: {
            $container: null
        },

        /**
         * Initialization
         */
        initialize: function (config, element) {
            this._super();
            this.$container = $(element);
            this.initEventHandlers();
            jQuery("#store-credit-error").hide();
            return this;
        },

        /**
         * Updated order totals section
         */
        updateTotals: function (event) {
            var data = {};
            if (event.currentTarget.id == 'p_method_use_customerbalance_partial') {
                if (event.currentTarget.checked) {
                    var amount = jQuery('#storecredit_amount').val();
                    var storeCreditType = 'partial';
                    var message = $t('please enter amount');
                    if (amount == '') {
                        jQuery("#store-credit-error").text(message);
                        jQuery("#store-credit-error").show();
                        jQuery("#p_method_use_customerbalance_partial").removeAttr("checked");
                        return false;
                    } else {
                        jQuery("#store-credit-error").hide();
                    }
                }
            } else {
                if (event.currentTarget.checked) {
                    var amount = '';
                    var storeCreditType = 'all';
                }
            }

            if (event.currentTarget.checked) {
                jQuery("#store-credit-error").hide();
                jQuery.ajax({
                    url: window.storeCreditBackUrl,
                    type: "POST",
                    data: {storeCreditAmount:amount,storeCreditType:storeCreditType},
                    showLoader: true,
                    cache: false,
                    success: function(response){
                        if (response.success == true) {
                            data['payment[use_customer_balance]'] = 1;
                            window.order.loadArea(['totals', 'billing_method'], true, data);
                            jQuery("#store-credit-error").hide();
                        } else {
                            var messageForIncorrect = $t('Enter amount is incorrect');
                            jQuery("#store-credit-error").text(messageForIncorrect);
                            jQuery("#store-credit-error").show();
                        }
                    }
                });
            } else {
                data['payment[use_customer_balance]'] = 0;
                window.order.loadArea(['totals', 'billing_method'], true, data);
            }
        },

        /**
         * Init event handlers
         */
        initEventHandlers: function () {
            this.$container.on('change', this.updateTotals.bind(this));
        }
    });
});
