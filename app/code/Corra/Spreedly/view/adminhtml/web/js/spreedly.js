/*browser:true*/
/*global define*/
define([
    'jquery',
    'uiComponent',
    'Magento_Ui/js/modal/alert',
    'Magento_Ui/js/lib/view/utils/dom-observer',
    'mage/translate'
], function ($, Class, alert, domObserver, $t) {
    'use strict';

    return Class.extend({

        defaults: {
            $selector: null,
            selector: 'edit_form',
            container: 'payment_form_spreedly',
            active: false,
            scriptLoaded: false,
            acceptjs: null,
            selectedCardType: null,
            imports: {
                onActiveChange: 'active'
            }
        },

        /**
         * Set list of observable attributes
         * @returns {exports.initObservable}
         */
        initObservable: function () {
          var self = this;
            self.scriptLoaded = true;

          self.$selector = $('#' + self.selector);
          this._super()
            .observe([
              'active',
              'selectedCardType'
            ]);

          // re-init payment method events
          self.$selector.off('changePaymentMethod.' + this.code)
              .on('changePaymentMethod.' + this.code, this.changePaymentMethod.bind(this));

          // listen block changes
         /* domObserver.get('#' + self.container, function () {
           if (self.scriptLoaded()) {
             self.$selector.off('submit');
            }
          });*/
            self.scriptLoaded = false;
          return this;
        },

        /**
         * Enable/disable current payment method
         * @param {Object} event
         * @param {String} method
         * @returns {exports.changePaymentMethod}
         */
        changePaymentMethod: function (event, method) {
            this.active(method === this.code);
            this.onActiveChange(this.active);
            return this;
        },

        /**
         * Triggered when payment changed
         * @param {Boolean} isActive
         */
        onActiveChange: function (isActive) {
            if (!isActive) {
                this.$selector.off('submitOrder.spreedly');

                return;
            }
            this.disableEventListeners();
            window.order.addExcludedPaymentMethod(this.code);

            /*if (!this.clientKey) {
              this.error($.mage.__('This payment is not available'));

              return;
            }*/

            this.enableEventListeners();

            /*if(!this.scriptLoaded()) {
              this.loadScript();
            }*/
        },

        /**
         * Show alert message
         * @param {String} message
         */
        error: function (message) {
            alert({
                content: message
            });
        },

        /**
         * Enable form event listeners
         */
        enableEventListeners: function () {
            this.$selector.on('submitOrder.spreedly', this.submitOrder.bind(this));
        },

        /**
         * Disable form event listeners
         */
        disableEventListeners: function () {
            this.$selector.off('submitOrder');
            this.$selector.off('submit');
        },

        /**
         * Trigger order submit
         */
        submitOrder: function () {
            var self = this;
            this.$selector.validate().form();
            this.$selector.trigger('afterValidate.beforeSubmit');
            $('body').trigger('processStop');

            // validate parent form
           /* if (this.$selector.validate().errorList.length) {
                $('body').trigger('processStop');
                return false;
            }*/

            if (order.paymentMethod == this.code) {
                var container = $('#' + self.container);
                container.find('[type="submit"]').trigger('click');
            } else {
                this.placeOrder();
            }
        },


        /**
         * Place order
         */
        placeOrder: function () {
            $('#' + this.selector).trigger('realOrder');
        },
    });
});
