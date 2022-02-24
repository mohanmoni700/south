define(
    [
        'Alfakher_HandlingFee/js/view/checkout/summary/handlingfee'
    ],
    function (Component) {
        'use strict';

        return Component.extend({

            /**
             * @override
             */
            isDisplayed: function () {
                return this.getValue() != 0;
            }
        });
    }
);