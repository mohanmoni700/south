/**
 * @author  CORRA
 */
define([
    'jquery',
    'uiComponent',
    'Magento_Ui/js/modal/alert'
], function ($, Class, alert) {
    'use strict';

    return Class.extend({
        defaults: {
            $selector: null,
            selector: 'edit_form',
            $container: null
        },

        initObservable: function () {
            var self = this;

            self.$selector = $('#' + self.selector);
            self.$container =  $('#' + self.container);
            self.$selector.on(
              'setVaultNotActive.' + self.getCode(),
              function () {
                  self.$selector.off('submitOrder.' + self.getCode());
              }
            );
            self._super();

            self.initEventHandlers();

            return self;
        },

        getCode: function () {
            return this.code;
        },

        initEventHandlers: function () {
            $(this.$container).find('[name="payment[token_switcher]"]')
              .on('click', this.selectPaymentMethod.bind(this));
        },

        selectPaymentMethod: function () {
            this.disableEventListeners();
            this.enableEventListeners();
        },

        enableEventListeners: function () {
            this.$selector.on('submitOrder.' + this.getCode(), this.submitOrder.bind(this));
        },

        disableEventListeners: function () {
            this.$selector.off('submitOrder');
        },

        submitOrder: function () {
            this.$selector.validate().form();
            this.$selector.trigger('afterValidate.beforeSubmit');
            $('body').trigger('processStop');

            // validate parent form
            if (this.$selector.validate().errorList.length) {
                return false;
            }

            // Start loader icon
            $('body').trigger('processStart');

            this.setPaymentDetails();
            this.placeOrder();

            // Stop loader icon
            $('body').trigger('processStop');
        },

        placeOrder: function () {
            this.$selector.trigger('realOrder');
        },

        setPaymentDetails: function () {
            this.$selector.find('[name="payment[public_hash]"]').val(this.publicHash);
        }
    });
});
