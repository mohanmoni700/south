/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
    [
        'jquery',
        'Magento_Checkout/js/view/payment/default',
        'Vrpayecommerce_Vrpayecommerce/js/action/set-payment-method',
        'Magento_Checkout/js/model/quote',
        'Magento_CheckoutAgreements/js/model/agreements-assigner'
    ],
    function ($, Component, setPaymentMethodAction, quote, agreementsAssigner) {
        'use strict';

        var isEasyCreditErrorAmount = function () {
            var quoteCurrencyCode = quote.totals()['base_currency_code'];
            var quoteGrandTotal = quote.totals()['base_grand_total'];
            if (quoteGrandTotal < 200 || quoteGrandTotal > 10000 || quoteCurrencyCode != "EUR") {
                return true;
            }
            return false;
        };

        var customerAddress = function () {
            var streets = quote.billingAddress().street;
            var complete_street = "";
            if (typeof streets !== 'undefined' && streets.length > 0) {
                streets.forEach(function(street){
                    
                    complete_street = complete_street + street + " ";
                });
                complete_street = complete_street + ", " + quote.billingAddress().postcode + ", " + quote.billingAddress().city;
            }
            return complete_street;
        }

        return Component.extend({
            defaults: {
                template: 'Vrpayecommerce_Vrpayecommerce/payment/vrpayecommerce-method'
            },
            /** Redirect to Payment Form */
            placeOrderAction: function () {
                var validate = true;
                if (this.getCode() == 'vrpayecommerce_klarnapaylater' || this.getCode() == 'vrpayecommerce_klarnasliceit') {
                    if ($('#'+'billing-address-same-as-shipping-'+this.getCode()).attr('checked') != 'checked') {
                        $('#'+this.getCode()+'_address_error').show();
                        validate = false;
                    } else {
                        $('#'+this.getCode()+'_address_error').hide();
                    }
                    if ($('#'+this.getCode()+'_term').attr('checked') != 'checked') {
                        $('#'+this.getCode()+'_term_error').show();
                        validate = false;
                    } else {
                        $('#'+this.getCode()+'_term_error').hide();
                    }
                }
                if(this.getCode() == 'vrpayecommerce_easycredit'){
                    var billingAddressSameAsShipping = document.querySelectorAll('[name=billing-address-same-as-shipping]');
                    
                    if (document.getElementById(this.getCode()+'_term').checked === false) {
                        $('#'+this.getCode()+'_term_error').show();
                        validate = false;
                    } else {
                        $('#'+this.getCode()+'_term_error').hide();
                    }
                    if (billingAddressSameAsShipping.length > 1) {
                        if (document.getElementById('billing-address-same-as-shipping-'+this.getCode()).checked === false) {
                            $('#'+this.getCode()+'_address_error').show();
                            validate = false;
                        } else {
                            $('#'+this.getCode()+'_address_error').hide();
                        }
                    } else {
                        if ($('[name=billing-address-same-as-shipping]').attr('checked') != 'checked') {
                            $('#'+this.getCode()+'_address_error').show();
                            validate = false;
                        } else {
                            $('#'+this.getCode()+'_address_error').hide();
                        }
                    }
                }
                if (!validate) {
                    return false;
                }
                var paymentData = {
                    method: this.getCode()
                };
                agreementsAssigner(paymentData);
                this.selectPaymentMethod(); // save selected payment method in Quote
                setPaymentMethodAction(
                    this.messageContainer,
                    paymentData
                );
                return false;
            },
            getLogos: function () {
                return window.checkoutConfig.payment.vrpayecommerce.logos[this.getCode()];
            },
            getPaymentTitle: function () {
                return window.checkoutConfig.payment.vrpayecommerce.title[this.getCode()];
            },
            getDetails: function () {
                return window.checkoutConfig.payment.vrpayecommerce.details[this.getCode()];
            },
            getEasyCreditErrorAmount: function () {
                return window.checkoutConfig.payment.vrpayecommerce.details[this.getCode()]['errorAmount'];
            },            
            renderKlarnaPaylaterTerms: function() {
                new Klarna.Terms.Invoice({
                    el: "klarna_invoice_terms",
                    eid: window.checkoutConfig.payment.vrpayecommerce.details['vrpayecommerce_klarnapaylater'].merchantId,
                    locale: "de_de",
                    charge: 0,
                    type: 'desktop'
                });
            },
            renderKlarnaSliceitTerms: function() {
                new Klarna.Terms.Account({
                    el: "klarna_account_terms",
                    eid: window.checkoutConfig.payment.vrpayecommerce.details['vrpayecommerce_klarnasliceit'].merchantId,
                    locale: "de_de",
                    type: 'desktop'
                });
            },
            renderKlarnaPaylaterConsent: function() {
                new Klarna.Terms.Consent({  
                    el: 'klarna_invoice_consent',
                    eid: window.checkoutConfig.payment.vrpayecommerce.details['vrpayecommerce_klarnapaylater'].merchantId,
                    locale: 'de_de',
                    type: 'desktop'
                });
            },
            renderKlarnaSliceItConsent: function() {
                new Klarna.Terms.Consent({  
                    el: 'klarna_account_consent',
                    eid: window.checkoutConfig.payment.vrpayecommerce.details['vrpayecommerce_klarnasliceit'].merchantId,
                    locale: 'de_de',
                    type: 'desktop'
                });
            },
            isDisabled: function() {
                var isAvailable = true;
                if(this.getCode() == 'vrpayecommerce_easycredit'){

                    var easycreditTermTextTemplate = window.checkoutConfig.payment.vrpayecommerce.details['vrpayecommerce_easycredit'].easycreditTerms;
                    var billingAddressFromCart = customerAddress();
                    var easycreditTermText = easycreditTermTextTemplate.replace("%y", billingAddressFromCart);
                    $('#vrpayecommerce_easycredit_term_error_text').text(easycreditTermText);
                    $('#gender').on('change', function() {
                        
                        if(this.value !=="" && !$("#vrpayecommerce_easycredit_errorAmount").is(":visible")){
                            $('#vrpayecommerce_easycredit').removeAttr("disabled");
                            $.cookie("gender", this.value);
                        } else {
                            $('#vrpayecommerce_easycredit').prop('disabled', true);
                            $('#vrpayecommerce_easycredit').prop('checked', false);
                        }

                    });
                    var billingAddressSameAsShipping = document.querySelectorAll('[name=billing-address-same-as-shipping]');
                    [].forEach.call(billingAddressSameAsShipping, function(elm){
                        $(elm).on('click', function() {
                            if ($(this).is(':checked')) {
                                $('#vrpayecommerce_easycredit_address_error').hide();        
                            }
                        });
                    });
                    var errorAmount = isEasyCreditErrorAmount();
                    var errorGender = window.checkoutConfig.payment.vrpayecommerce.details['vrpayecommerce_easycredit'].errorGender;
                    
                    if(errorAmount){
                        $('#vrpayecommerce_easycredit').prop('disabled', true);
                        $('#vrpayecommerce_easycredit').prop('checked', false);
                        $("#vrpayecommerce_easycredit_errorAmount").show();
                        $('#vrpayecommerce_easycredit_payment_content').hide();
                        isAvailable = false;
                    }else{
                        if (quote.paymentMethod()) {
                            if (quote.paymentMethod()['method'] == 'vrpayecommerce_easycredit'){
                                $('#vrpayecommerce_easycredit_payment_content').show();
                            } else {
                                $('#vrpayecommerce_easycredit_payment_content').hide();
                            }
                        }
                        $("#vrpayecommerce_easycredit_errorAmount").hide();
                    }

                    if(errorGender != ''){
                        $("#vrpayecommerce_easycredit_errorGender").show();
                        isAvailable = false;
                    }else{
                        $("#vrpayecommerce_easycredit_errorGender").hide();
                        $("#field_gender").hide();
                    }
                }

                return isAvailable;
            },
            easyCreditTermInit: function() {
                var paymentRadio = document.querySelectorAll('.payment-group .payment-method input[type=radio]');
                [].forEach.call(paymentRadio, function(elm) {
                    if ($(elm).closest('.payment-method').hasClass('_active')) {
                        if ($(elm).val() == 'vrpayecommerce_easycredit') {
                            $('#easycreditTerms').show();
                        }
                    }
                    $(elm).click(function() {
                        if (this.value == 'vrpayecommerce_easycredit') {
                            $('#easycreditTerms').show();
                        } else {
                            $('#easycreditTerms').hide();
                        }
                    });
                });
            },
            enterPayTermInit: function() {
                var paymentRadio = document.querySelectorAll('.payment-group .payment-method input[type=radio]');
                [].forEach.call(paymentRadio, function(elm) {
                    if ($(elm).closest('.payment-method').hasClass('_active')) {
                        if ($(elm).val() == 'vrpayecommerce_enterpay') {
                            $('#enterpayTerms').show();
                        }
                    }
                    $(elm).click(function() {
                        if (this.value == 'vrpayecommerce_enterpay') {
                            $('#enterpayTerms').show();
                        } else {
                            $('#enterpayTerms').hide();
                        }
                    });
                });
            }
        });
    }
);
