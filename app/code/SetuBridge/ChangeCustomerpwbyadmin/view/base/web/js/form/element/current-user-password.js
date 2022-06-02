define([
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/form/element/abstract',
    'Magento_Ui/js/lib/validation/validator',
    'mageUtils'
    ], function (_, uiRegistry, Abstract,validator,utils) {
        'use strict';
        return Abstract.extend({  
            defaults: {
                visible: true,
                label: '',
                error: '',
                uid: utils.uniqueid(),
                disabled: false,
                links: {
                    value: '${ $.provider }:${ $.dataScope }'
                }
            },
            initialize: function () {
                this._super();
                this.visible(false);
                return this;
            },    
            validate: function () {
                var newpw=uiRegistry.get('index=new_password').get('value');
                var value = this.value(),
                result = validator(this.validation, value, this.validationParams),
                message = !this.disabled() && this.visible() ? result.message : '',
                isValid = this.disabled() || !this.visible() || result.passed;
                this.error(message);
                this.bubble('error', message);
                if(newpw.length > 0 && value.length==0){
                    isValid=false;  
                }else{
                    isValid=true; 
                }
                //TODO: Implement proper result propagation for form
                if (!isValid) {
                    this.source.set('params.invalid', true);
                }
                return {
                    valid: isValid,
                    target: this
                };
            }
        });
});