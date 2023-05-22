<?php

namespace Avalara\Excise\Plugin;

use Magento\Checkout\Block\Checkout\LayoutProcessor;

class CountyPlugin
{
    public function newafterProcess(LayoutProcessor $subject, $jsLayout)
    {
        $customAttributeCode = 'county';
        $customShippingField = [
            'component' => 'Magento_Ui/js/form/element/abstract',
            'config' => [
                // customScope is used to group elements within a single form (e.g. they can be validated separately)
                'customScope' => 'shippingAddress.custom_attributes',
                'customEntry' => null,
                'template' => 'ui/form/field',
                'elementTmpl' => 'ui/form/element/input'
            ],
            'dataScope' => 'shippingAddress.custom_attributes' . '.' . $customAttributeCode,
            'label' => 'County Plugin Ship',
            'provider' => 'checkoutProvider',
            'sortOrder' => 96,
            'validation' => [
                'required-entry' => false
            ],
            'options' => [],
            'filterBy' => null,
            'customEntry' => null,
            'visible' => true,
            'value' => '123'
        ];

        $jsLayout['components']['checkout']['children']['steps']
                ['children']['shipping-step']['children']['shippingAddress']
                ['children']['shipping-address-fieldset']
                ['children'][$customAttributeCode] = $customShippingField;
        
        $customBillingField = [
            'component' => 'Magento_Ui/js/form/element/abstract',
            'config' => [
                'customScope' => 'billingAddress.custom_attributes',
                'customEntry' => null,
                'template' => 'ui/form/field',
                'elementTmpl' => 'ui/form/element/input'
            ],
            'dataScope' => 'billingAddress.custom_attributes.' . $customAttributeCode,
            'label' => 'County Plugin Bill',
            'provider' => 'checkoutProvider',
            'sortOrder' => 96,
            'validation' => [
                'required-entry' => false
            ],
            'options' => [],
            'filterBy' => null,
            'customEntry' => null,
            'visible' => true,
            'value' => '321-bill'
        ];
        
        $configuration = $jsLayout['components']['checkout']['children']['steps']
                ['children']['billing-step']['children']['payment']
                ['children']['payments-list']['children'];
        foreach ($configuration as $paymentGroup => $groupConfig) {
            if (isset($groupConfig['component'])
                && $groupConfig['component'] === 'Magento_Checkout/js/view/billing-address') {
                $jsLayout['components']['checkout']
                    ['children']['steps']['children']['billing-step']
                    ['children']['payment']['children']['payments-list']
                    ['children'][$paymentGroup]['children']['form-fields']
                    ['children'][$customAttributeCode] = $customBillingField;
            }
        }

        return $jsLayout;
    }
}
