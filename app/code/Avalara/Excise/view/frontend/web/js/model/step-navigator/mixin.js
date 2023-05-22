
define(['jquery'], function (jQuery) {
    'use strict';

    return function (stepNavigatorModule) {
        var parentNavigateTo = stepNavigatorModule.navigateTo;

        // Wrap the native navigate function so that we can trigger a jQuery event to listen to
        stepNavigatorModule.navigateTo = function navigateTo()
        {
            parentNavigateTo.apply(this, arguments);

            jQuery(window.document).trigger('checkout.navigateTo');
        };

        return stepNavigatorModule;
    };
});
