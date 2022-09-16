define(
    [
        'Magento_Checkout/js/view/payment/default',
		'jquery'
    ],
    function (Component,$) {
        'use strict';
 
        return Component.extend({
            defaults: {
                template: 'Alfakher_Seamlesschex/payment/seamlesschex'
            },

			getData: function () {
	            return {
	                method: this.item.method,
	                'additional_data': {
	                    'accountnumber': $('#ach_accountnumber').val(),
	                    'routingnumber': $('#ach_routingnumber').val(),
	                    'checknumber': $('#ach_check_number').val()
	                }
	            };
	        },

			validate: function () {
	            var form = 'form[data-role=ach_account_info_form]';

	            return $(form).validation() && $(form).validation('isValid');
	        },
        });
    }
);