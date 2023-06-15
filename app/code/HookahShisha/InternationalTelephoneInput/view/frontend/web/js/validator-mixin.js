define([
    'jquery',
    'moment'
], function ($, moment) {
    'use strict';
    return function (validator) {
        validator.addRule(
            'custom-validate-telephone',
            function (value, params) {
                let phoneno1 = /^(\+\d{1,2}\s)?\(?\d{3}\)?[\s.-]\d{3}[\s.-]\d{4}$/;
                let phoneno2 = /^\+?\d+$/;
                let phoneno3 = /^\+\d{1,4}\s\d+$/;
                if((value.match(phoneno1)) || (value.match(phoneno2)) || (value.match(phoneno3))){
                    return true;
                }
            },
            $.mage.__("Please enter the valid phone number.")
        );
        return validator;
    };
});
