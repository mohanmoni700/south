define([
    'jquery',
    'underscore',
    'moment',
    'mage/translate'
], function ($, _, moment) {
    'use strict';

    return function (validator) {
        var validators = {
            'validate-country-code-number': [
                function (value) {
                    return /^[+]([[0-9 ]{1,})?([1-9 ][0-9])$/.test(value);
                },
                $.mage.__('Please add a valid number')
            ]
        };

        validators = _.mapObject(validators, function (data) {
            return {
                handler: data[0],
                message: data[1]
            };
        });

        return $.extend(validator, validators);
    };
});