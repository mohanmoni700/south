
define([
    'jquery',
    'ko',
    'Avalara_Excise/js/model/address-model',
    'Avalara_Excise/js/view/address-validation-form',
    'Magento_Checkout/js/model/checkout-data-resolver',
    'Magento_Checkout/js/action/select-billing-address',
    'Magento_Checkout/js/action/create-billing-address',
    'Magento_Checkout/js/model/quote'
], function (
    $,
    ko,
    addressModel,
    addressValidationForm,
    checkoutDataResolver,
    selectBillingAddress,
    createBillingAddress,
    quote
) {

    $.widget('Avalara_Excise.checkoutBillingAddressValidationModal', $.mage.modal, {
        validationContainer: '.billingValidationModal .modal-content > div',
        formSelector: '.billing-address-form form',
        options: {
            title: $.mage.__('Verify Your Billing Address'),
            modalClass: 'billingValidationModal',
            focus: '.billingValidationModal .action-primary',
            responsive: true,
            closeText: $.mage.__('Close'),
            buttons: [
                {
                    text: $.mage.__('Edit Address'),
                    class: 'action-secondary action-dismiss',
                    click: function () {
                        this.editAddress();
                    }
            },
                {
                    text: $.mage.__('Save Address'),
                    class: 'action-primary action primary',
                    click: function () {
                        var selAdd = addressModel.selectedAddress();
                        if (selAdd.custom_attributes === undefined) {
                            var custAttr = [];
                            if (selAdd.extension_attributes != undefined) {
                                //selAdd.custom_attributes = selAdd.extension_attributes;
                            }
                        }
                        if (addressModel.isDifferent()) {
                            var convertedAdd = createBillingAddress(selAdd);
                            if (selAdd.extension_attributes != undefined && convertedAdd.extension_attributes === undefined) {
                                convertedAdd.extension_attributes = selAdd.extension_attributes;
                            }
                            selectBillingAddress(convertedAdd);
                            checkoutDataResolver.applyBillingAddress();
                            addressValidationForm.updateFormFields(this.formSelector);
                        }
                        window.checkoutConfig.billingAddressValidation.isAddressValid = true;
                        this.clickNativePlaceOrder();
                        this.closeModal();
                    }
            }
            ]
        },

        _create: function () {
            this._super();
            addressValidationForm.bindTemplate(this.validationContainer, this.options, 'Avalara_Excise/baseValidateAddress');
        },

        openModal: function () {
            this._super();
            var self = this;
            $(this.validationContainer + " .edit-address").on('click', function () {
                self.editAddress();
            });
        },

        closeModal: function () {
            this._super();
        },

        editAddress: function () {
            var self = this;
            self.clickNativeEditBillingAddress();
            window.checkoutConfig.billingAddressValidation.isAddressValid = false;
            self.closeModal();
        },

        clickNativePlaceOrder: function () {
            $('.payment-method._active button[type=submit].checkout').click();
        },

        clickNativeEditBillingAddress: function () {
            $('.payment-method._active .action-edit-address').click();
        }
    });

    return $.Avalara_Excise.checkoutBillingAddressValidationModal;
});
