require(
    [
        'jquery'
    ],
    function ($) {
        var subscriptionAttributes = [
            'subscription_type', 'discount_type', 'discount_amount', 'initial_amount', 'billing_period_type',
            'billing_period', 'day_of_month', 'billing_max_cycles', 'define_start_from', 'allow_update_date',
            'allow_trial', 'allow_subscription_end_date'
        ];
        var trialAttributes = ['trial_period', 'trial_amount', 'trial_maxcycle'];

        isShowAttributeField = function (show) {
            subscriptionAttributes.forEach(function (element) {
                var currrentField = jQuery("div").find('[data-index="' + element + '"]');
                if (show) {
                    currrentField.show();
                } else {
                    currrentField.hide();
                }
            });
            isShowTrialField(show);
        };

        isShowTrialField = function (show) {
            trialAttributes.forEach(function (element) {
                var currrentField = jQuery("div").find('[data-index="' + element + '"]');
                if (show) {
                    currrentField.show();
                } else {
                    currrentField.hide();
                }
            });
        };

        onValueHideFields = function () {
            var billingPeriodType = jQuery('[name="product[billing_period_type]"]').val();
            var startfrom = jQuery('[name="product[define_start_from]"]').val();
            var trialCheck = jQuery('[name="product[allow_trial]"]').val();
            var subscriptionCheck = jQuery('[name="product[is_subscription]"]').val();

            if (subscriptionCheck == 1 && billingPeriodType == 'admin') {
                jQuery("div").find("[data-index='billing_period']").show();
            } else {
                jQuery("div").find("[data-index='billing_period']").hide();
            }

            if (trialCheck == 0) {
                isShowTrialField(false);
            }

            if (startfrom != 'exact_day_month') {
                jQuery("div").find("[data-index='day_of_month']").hide();
            }

            /*if (startfrom != 'defined_by_customer') {
                jQuery("div").find("[data-index='allow_subscription_end_date']").hide();
            }*/

            //jQuery("div").find("[data-index='day_of_month']").hide();

            if (subscriptionCheck == 0) {
                isShowAttributeField(false);
            }
        };

        jQuery(document).delegate(jQuery("[data-index='is_subscription'] select"), "change", function (element) {
            if (element.target.name == "product[is_subscription]") {
                if (jQuery('[name="product[is_subscription]"]').val() == 1) {
                    isShowAttributeField(true);
                } else {
                    isShowAttributeField(false);
                }
            }
        });

        jQuery(document).delegate(jQuery("[data-index='allow_trial'] select"), "change", function (element) {
            if (element.target.name == "product[allow_trial]") {
                if (jQuery('[name="product[allow_trial]"]').val() == 1) {
                    isShowTrialField(true);
                } else {
                    isShowTrialField(false);
                }
            }
        });

        jQuery(document).delegate(jQuery("[data-index='billing_period_type'] select"), "change", function (element) {
            if (element.target.name == "product[billing_period_type]") {
                if (jQuery('[name="product[billing_period_type]"]').val() == 'admin') {
                    jQuery("div").find("[data-index='billing_period']").show();
                } else {
                    jQuery("div").find("[data-index='billing_period']").hide();
                }
            }
        });

        jQuery(document).delegate(jQuery("[data-index='define_start_from'] select"), "change", function (element) {
            if (element.target.name == "product[define_start_from]") {
                if (jQuery('[name="product[define_start_from]"]').val() == 'exact_day_month') {
                    jQuery("div").find("[data-index='day_of_month']").show();
                } else {
                    jQuery("div").find("[data-index='day_of_month']").hide();
                }

                /*if (jQuery('[name="product[define_start_from]"]').val() == 'defined_by_customer') {
                    jQuery("div").find("[data-index='allow_subscription_end_date']").show();
                } else {
                    jQuery("div").find("[data-index='allow_subscription_end_date']").hide();
                }*/
            }
        });

        jQuery(document).delegate(jQuery(".fieldset-wrapper-title"), "click", function (element) {
            onValueHideFields();
        });

        jQuery(document).ajaxComplete(function () {
            onValueHideFields();
        });
    }
);
