define([
    'jquery',
    'jquery/validate',
    'mage/translate'
    ], function($) {
        'use strict';

        return function() {
            $.validator.addMethod(
                'wholesalers-validate-text',
                function (value, params) {
                    var text = /[^\w- ]|_/g;

                    if((value.match(text))){
                        return false;
                    }
                    return true;
                },
                $.mage.__("Not Enter Special Characters")
            );

            $.validator.addMethod(
                'country-code-validate-num',
                function (value, params) {
                    var num = /^[+]([[0-9 ]{1,})?([1-9 ][0-9])$/;
                    if(value.match(num)){
                        return true;
                    }
                    return false;
                },
                $.mage.__("Please enter a valid number")
            );

             $.validator.addMethod(
                'shisha-validate-name',
                function (value, params) {
                     if(!value.match(/\d+/)){
                        return true
                     }
                    return false;
                },
                $.mage.__("Number not allowed")
            );
        }
        
    });