define(
    [
        'jquery',
        'MageWorx_OrderEditor/js/order/edit/form/base',
        'jquery/ui'
    ],
    function ($) {
        'use strict';

        $.widget('mage.mageworxOrderEditorPayment', $.mage.mageworxOrderEditorBase, {
            params: {
                updateUrl: '',
                loadFormUrl: '',

                cancelButtonId: '#payment-method-cancel',
                submitButtonId: '#payment-method-submit',
                editLinkId: '#ordereditor-payment-link',

                blockId: 'payment_method',
                formId: '#payment-form',
                blockContainerId: '.admin__page-section-item-content',
                formContainerId: '.order-payment-method',
                linkContainerId: '.admin__page-section-item-title',
                magentoLoadBlockUrl: '',

                paymentMethodBlockId: '#order-payment-method-choose'
            },

            init: function (params) {
                this.params = this._mergeParams(this.params, params);
                this._initParams(params);
                if (params.isAllowed){this._initActions();}
            },

            getLoadFormParams: function () {
                var orderId = this.getCurrentOrderId();
                var blockId = this.params.blockId;
                return {'form_key': FORM_KEY, 'order_id': orderId, 'block_id': blockId};
            },

            validateForm: function () {
                var validate = $(this.params.paymentMethodBlockId).find('input[name="payment[method]"]:checked').length == 1;
                if($(this.params.paymentMethodBlockId).find('input[name="payment[method]"]:checked').val() == 'paradoxlabs_firstdata'){
                    var card_id= $('#paradoxlabs_firstdata-card-id').find(":selected").val() != "";
                    var ccNumber= $('#paradoxlabs_firstdata-cc-number').val().length > 0;
                    var ccExpMonth= $('#paradoxlabs_firstdata-cc-exp-month').find(":selected").val() != "";
                    var ccExpYear= $('#paradoxlabs_firstdata-cc-exp-year').find(":selected").val() != "";
                    var ccCid= $('#paradoxlabs_firstdata-cc-cid').val().length > 0;
                    
                    if(card_id || (ccNumber && ccExpMonth && ccExpYear && ccCid  && $('#paradoxlabs_firstdata-cc-cid').hasClass("valid"))){
                        return true;
                    }else{
                        return false;
                    }
                }else{
                    return validate;
                }
            },

            getConfirmUpdateData: function () {
                var self = this;
                var orderId = this.getCurrentOrderId();
                var paymentMethod = $(self.params.paymentMethodBlockId)
                    .find('input[name="payment[method]"]:checked')
                    .val();
                
                
                var card_id= $('#paradoxlabs_firstdata-card-id').find(":selected").val();
                var ccNumber= $('#paradoxlabs_firstdata-cc-number').val();
                var ccExpMonth= $('#paradoxlabs_firstdata-cc-exp-month').find(":selected").val();
                var ccExpYear= $('#paradoxlabs_firstdata-cc-exp-year').find(":selected").val();
                var ccCid= $('#paradoxlabs_firstdata-cc-cid').val();
                var ccType= $('#paradoxlabs_firstdata-cc-type').val();
                var save= $('#paradoxlabs_firstdata-save').val();
                
                var params = {'form_key': FORM_KEY, 'payment_method': paymentMethod, 'order_id': orderId, 
                'card_id': card_id, 'cc_number':ccNumber, 'cc_exp_month':ccExpMonth, 'cc_exp_year':ccExpYear, 
                'cc_cid':ccCid, 'cc_type':ccType, 'save':save};

                $(['payment_title', 'payment_comment']).each(function (j, i) {
                    params[i] = $(self.params.paymentMethodBlockId)
                        .find('input[name="payment[' + paymentMethod + '][' + i + ']"]')
                        .val();
                });

                $(['authorizenet_directpost_cc_type',
                    'authorizenet_directpost_cc_number',
                    'authorizenet_directpost_expiration',
                    'authorizenet_directpost_expiration_yr'
                ]).each(function (j, i) {
                    params[i] = $('#' + i + '').val();
                });

                return params;
            },

            initInput: function () {
                var self = this;
                var input = this.params.paymentMethodBlockId + ' input[type="text"]';
                var radio = this.params.paymentMethodBlockId + ' input[type="radio"]';

            },
        });

        return $.mage.mageworxOrderEditorPayment;
    }
);
