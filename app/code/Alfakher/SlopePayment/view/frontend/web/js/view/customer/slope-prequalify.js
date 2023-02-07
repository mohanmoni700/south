define([
    'jquery',
    'mage/url',
    'Magento_Ui/js/modal/alert',
    'mage/translate'
], function ($, url, alert, $t) {
    'use strict';

    return function () {
        $('#slope-prequalify').on('click', function () {
            var apiUrl = url.build("slope/customer/prequalify");
            $.ajax({
                url: apiUrl,
                type: 'GET',
                showLoader: true,
                success: function (data) {
                    if(data.success === false)
                    {
                        const messages = data.messages[0];
                        var errorContent = "<ul>\n";
                        messages.forEach(function(message) {
                          errorContent += "<li>" + $t(message) + "</li>\n";
                        });
                        errorContent += "</ul>";
                        alert({
                            title: $t('Slope Pre-Qualification'),
                            content: errorContent,
                        });
                        return false;
                    }
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
