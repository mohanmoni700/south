define([
    'jquery',
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/form/element/abstract',
    'mageUtils'
    ], function ($,_, uiRegistry, Abstract,utils) {
        'use strict';
        return Abstract.extend({  
            defaults: {
                visible: true,
                label: '',
                error: '',
                valueUpdate:'keyup',
                uid: utils.uniqueid(),
                disabled: false,
                links: {
                    value: '${ $.provider }:${ $.dataScope }'
                }
            },
            /**
            * On value change handler.
            *
            * @param {String} value
            */
            onUpdate: function (value) {
                this.verifyCurrentUserIdentity(value);
                return this._super();
            },
            verifyCurrentUserIdentity: function(value){
                var currentUserPassword = uiRegistry.get('index=current_user_password');
                if(value){
                    currentUserPassword.set('visible',true);
                }else{
                    currentUserPassword.set('visible',false);
                }  
            }
        });
});