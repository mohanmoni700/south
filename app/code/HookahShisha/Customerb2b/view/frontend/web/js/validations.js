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
                    // var text = /[^\w]|_/g;
                    var text = /[^\w-]|_/g;

                    if((value.match(text))){
                        return false;
                    }else{
                        return true;
                    }                        
                },
                $.mage.__("Not Enter Special Characters")
            );
        }
    });