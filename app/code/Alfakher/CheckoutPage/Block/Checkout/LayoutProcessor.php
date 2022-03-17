<?php
namespace Alfakher\CheckoutPage\Block\Checkout;

class LayoutProcessor
{
    public function afterProcess(
        \Magento\Checkout\Block\Checkout\LayoutProcessor $subject,
        array $jsLayout
    ) {
        $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
        ['children']['shippingAddress']['children']['shipping-address-fieldset']['children']['street']['children'][0]['label'] = __('Address Line 1*');

        $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
        ['children']['shippingAddress']['children']['shipping-address-fieldset']['children']['street']['children'][1]['label'] = __('Address Line 2');

        return $jsLayout;
    }
}
