var config = {
    paths: {
        "intlTelInput": 'HookahShisha_InternationalTelephoneInput/js/intlTelInput',
        "intlTelInputUtils": 'HookahShisha_InternationalTelephoneInput/js/utils',
        "internationalTelephoneInput": 'HookahShisha_InternationalTelephoneInput/js/internationalTelephoneInput'
    },

    shim: {
        'intlTelInput': {
            'deps':['jquery', 'knockout']
        },
        'internationalTelephoneInput': {
            'deps':['jquery', 'intlTelInput']
        }
    },
    config: {
        mixins: {
            'Magento_Ui/js/lib/validation/validator': {
                'HookahShisha_InternationalTelephoneInput/js/validator-mixin': true
            }
        }
    }
};
