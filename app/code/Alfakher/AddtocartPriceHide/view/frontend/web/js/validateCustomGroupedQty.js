/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
'jquery',
   'jquery/ui',
   'jquery/validate',
   'mage/translate'
], function($){
    'use strict';
 
    return function() {
        $.validator.addMethod(
            "validate-custom-grouped-qty",
            function (value, element, params) {
                var result = false,
                    total = 0;

                $(params).find('select[data-validate*="validate-custom-grouped-qty"]').each(function (i, e) {
                    var val = $(e).val(),
                        valInt;

                    if (val && val.length > 0) {
                        result = true;
                        valInt = parseFloat(val) || 0;

                        if (valInt >= 0) {
                            total += valInt;
                        } else {
                            result = false;

                            return result;
                        }
                    }
                });

                return result && total > 0;
            },
            $.mage.__('Please specify the quantity of product(s).')
        );
    }
});
