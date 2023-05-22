
define([
    'jquery',
    'Magento_Ui/js/form/element/abstract'
], function (
    $,
    Abstract
) {
    'use strict';

    return Abstract.extend({
        defaults: {
            links: {
                value: ''
            },
            template: 'Avalara_Excise/form/element/adminValidateAddress'
        },
        baseTemplate: 'Avalara_Excise/baseValidateAddress',
        choice: 1,

        initialize: function () {
            this._super()
                .initFormId();
            $(document).on('click', '.validateAddressForm .instructions[data-uid="' + this.uid + '"] .edit-address', function () {
                $('html, body').animate({scrollTop: $("#container").offset().top}, 1000);
            });

            return this;
        },

        initFormId: function () {
            var namespace;

            if (this.formId) {
                return this;
            }

            namespace = this.name.split('.');
            this.formId = namespace[0];

            return this;
        },

        getBaseValidateAddressTemplate: function () {
            return this.baseTemplate;
        }
    });
});
