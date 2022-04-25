<?php
namespace Alfakher\CheckoutPage\Plugin\Block\Checkout;

class AttributeMerger
{
    /**
     * AttributeMerger
     *
     * @param \Magento\Checkout\Block\Checkout\AttributeMerger $subject
     * @param string $result
     */
    public function afterMerge(
        \Magento\Checkout\Block\Checkout\AttributeMerger $subject,
        $result
    ) {
        $result['firstname']['placeholder'] = __('First Name');
        $result['lastname']['placeholder'] = __('Last Name');
        $result['street']['children'][0]['placeholder'] = __('Address Line 1');
        $result['street']['children'][1]['placeholder'] = __('Address Line 2');
        $result['city']['placeholder'] = __('City');
        $result['postcode']['placeholder'] = __('Postal Code');
        $result['telephone']['placeholder'] = __('Phone Number');
        $result['county']['placeholder'] = __('County');
        $result['company']['placeholder'] = __('Company Name');
        return $result;
    }
}
