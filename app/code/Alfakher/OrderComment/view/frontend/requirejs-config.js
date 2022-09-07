var config = {
	config: {
	    mixins: {
	        'Magento_Checkout/js/action/place-order': {
	            'Alfakher_OrderComment/js/order/place-order-mixin': true
	        },
	        'Magento_Checkout/js/action/set-payment-information': {
	            'Alfakher_OrderComment/js/order/set-payment-information-mixin': true
	        }
	    }
	}
};