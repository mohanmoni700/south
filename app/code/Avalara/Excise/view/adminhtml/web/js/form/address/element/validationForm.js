
define([
    'jquery',
    'Magento_Ui/js/form/element/abstract'
], function ($, Abstract) {
    'use strict';

    return Abstract.extend({
        defaults: {
            template: 'Avalara_Excise/form/address/element/adminValidateAddress'
        },
        baseTemplate: 'Avalara_Excise/form/address/element/baseValidateAddress',
        choice: 1,

        /**
         * @inheritdoc
         */
        initialize: function () {
            this._super();
            $(document).on('click', '.exciseValidateAddressForm .instructions[data-uid="' + this.uid + '"] .edit-address', function () {
                $('.modal-inner-wrap').animate({scrollTop: $('.modal-slide').offset().top}, 1000);
            });
            return this;
        },

        /**
         * @returns {string}
         */
        getBaseValidateAddressTemplate: function () {
            return this.baseTemplate;
        }
    });
});
