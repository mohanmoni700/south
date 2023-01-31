define([
    'jquery',
    'mage/url'
], function ($, url) {
    'use strict';

    return function () {
        $('#slope-prequalify').on('click', function () {
            var apiUrl = url.build("slope/customer/prequalify");
            $.ajax({
                url: apiUrl,
                type: 'GET',
                showLoader: true,
                success: function (data) {
                    window.initializeSlope({
                        flow: 'pre_qualify',
                        intentSecret: data.secret,
                    });
                    window.Slope.open();
                }
            });
        });
    }
});
