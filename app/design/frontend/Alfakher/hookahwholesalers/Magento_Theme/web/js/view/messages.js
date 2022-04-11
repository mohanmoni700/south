/**
 * Copyright © 2020 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'uiComponent',
    'Magento_Customer/js/customer-data',
    'jquery/jquery-storageapi'
], function ($, Component, customerData) {
    'use strict';

    return Component.extend({
        initialize: function () {
            this._super();
            this.cookieMessages = $.cookieStorage.get('mage-messages');
            this.messages = customerData.get('messages').extend({disposableCustomerData: 'messages'});
            $.cookieStorage.setConf({path: '/', expires: -1}).set('mage-messages', null);
        },
        beforeViewConstruction: function (selector) {

        },
        afterRender: function (type, selector) {
            $(selector).addClass('animate-massage');
            this.onClose(type, selector);
        },
        onClose : function(type, selector){
            setTimeout(function closeNotificationMessage() {
                $(selector).slideUp(1000, function () {
                    $(this).removeClass('message-' + type + ' ' + type + ' animate-massage');
                });
            }, 10000);
        }
    });
});
