define([
    'jquery',
    'uiComponent',
    'Magento_Customer/js/customer-data',
    'underscore',
    'escaper'
], function (customerData) {
    'use strict';

    return function (target) {
        return target.extend({
            /**
             * Extends Component object by storage observable messages.
             */
            initialize: function () {
                this._super();

            setTimeout(function() {
                jQuery('.message-error.error.message').fadeOut('fast');
            }, 10000);

            }
        });
    }
});