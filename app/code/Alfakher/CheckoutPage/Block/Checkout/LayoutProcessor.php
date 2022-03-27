<?php
namespace Alfakher\CheckoutPage\Block\Checkout;

class LayoutProcessor
{
    public function afterProcess(
        \Magento\Checkout\Block\Checkout\LayoutProcessor $subject,
        array $jsLayout
    ) {
        /*For shipping address form*/
        $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
        ['children']['shippingAddress']['children']['shipping-address-fieldset']['children']['street']['children'][0]['label'] = __('Address Line 1*');

        $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
        ['children']['shippingAddress']['children']['shipping-address-fieldset']['children']['street']['children'][1]['label'] = __('Address Line 2');

        /*For billing address form change lable*/
        /* config: checkout/options/display_billing_address_on = payment_method */
        if (isset($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
            ['payment']['children']['payments-list']['children']
        )) {

            foreach ($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
                ['payment']['children']['payments-list']['children'] as $key => $payment) {

                if (isset($payment['children']['form-fields']['children']['street'])) {

                    $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
                    ['payment']['children']['payments-list']['children'][$key]['children']['form-fields']['children']['street']['children'][0]['label'] = __('Address Line 1*');

                    $jsLayout['components']['checkout']['children']['steps']['children']
                    ['billing-step']['children']
                    ['payment']['children']['payments-list']['children'][$key]['children']
                    ['form-fields']['children']['street']['children'][1]['label'] = __('Address Line 2');

                }

                /*For billing address Validation*/
                if (isset($payment['children']['form-fields']['children']['firstname'])) {
                    $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
                    ['payment']['children']['payments-list']['children'][$key]['children']['form-fields']['children']
                    ['firstname']['validation'] = ['required-entry' => true, 'letters-only' => true];
                }
                if (isset($payment['children']['form-fields']['children']['lastname'])) {
                    $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
                    ['payment']['children']['payments-list']['children'][$key]['children']['form-fields']['children']
                    ['lastname']['validation'] = ['required-entry' => true, 'letters-only' => true];
                }

                if (isset($payment['children']['form-fields']['children']['telephone'])) {
                    $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
                    ['payment']['children']['payments-list']['children'][$key]['children']['form-fields']['children']
                    ['telephone']['validation'] = ['required-entry' => true, 'validate-number' => true];
                }

            }
        }

        return $jsLayout;
    }
}
