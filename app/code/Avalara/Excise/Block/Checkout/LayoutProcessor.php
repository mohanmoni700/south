<?php
/**
 * Test
 * Copyright (C) 2019
 *
 * This file included in Avalara/Excise is licensed under OSL 3.0
 *
 * http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * Please see LICENSE.txt for the full text of the OSL 3.0 license
 */

namespace Avalara\Excise\Block\Checkout;

class LayoutProcessor implements \Magento\Checkout\Block\Checkout\LayoutProcessorInterface
{

    public function getShippingFormFields($result)
    {
        if (isset($result['components']['checkout']['children']['steps']['children']
            ['shipping-step']['children']['shippingAddress']['children']
            ['shipping-address-fieldset'])
        ) {
            $customShippingFields = $this->getFields('shippingAddress.custom_attributes', 'shipping');
        
            $shippingFields = $result['components']['checkout']['children']['steps']['children']
            ['shipping-step']['children']['shippingAddress']['children']
            ['shipping-address-fieldset']['children'];
        
            $shippingFields = array_replace_recursive($shippingFields, $customShippingFields);
        
            $result['components']['checkout']['children']['steps']['children']
            ['shipping-step']['children']['shippingAddress']['children']
            ['shipping-address-fieldset']['children'] = $shippingFields;
        }

        return $result;
    }

    public function getBillingFormFields($result)
    {
        if (isset($result['components']['checkout']['children']['steps']['children']
            ['billing-step']['children']['payment']['children']
            ['payments-list'])
        ) {
            $paymentForms = $result['components']['checkout']['children']['steps']['children']
            ['billing-step']['children']['payment']['children']
            ['payments-list']['children'];
        
            foreach ($paymentForms as $paymentMethodForm => $paymentMethodValue) {
                $paymentMethodCode = str_replace('-form', '', $paymentMethodForm);
        
                if (!isset($result['components']['checkout']
                            ['children']['steps']['children']['billing-step']
                            ['children']['payment']['children']['payments-list']
                            ['children'][$paymentMethodCode . '-form'])) {
                    continue;
                }
        
                $billingFields = $result['components']['checkout']['children']['steps']['children']
                ['billing-step']['children']['payment']['children']
                ['payments-list']['children'][$paymentMethodCode . '-form']['children']['form-fields']['children'];
        
                $customBillingFields = $this->getFields('billingAddress' . $paymentMethodCode . '.custom_attributes', 'billing');
        
                $billingFields = array_replace_recursive($billingFields, $customBillingFields);
        
                $result['components']['checkout']['children']['steps']['children']
                ['billing-step']['children']['payment']['children']
                ['payments-list']['children'][$paymentMethodCode . '-form']
                ['children']['form-fields']['children'] = $billingFields;
            }
        }

        return $result;
    }

    public function process($result)
    {
        $result = $this->getShippingFormFields($result);
        $result = $this->getBillingFormFields($result);

        return $result;
    }

    public function getFields($scope, $addressType)
    {
        $fields = [];
        foreach ($this->getAdditionalFields($addressType) as $field) {
            $fields[$field] = $this->getField($field, $scope);
        }

        return $fields;
    }

    public function getField($attributeCode, $scope)
    {
        $field = [
            'config' => [
                'customScope' => $scope,
                'template' => 'ui/form/field',
                'elementTmpl' => 'ui/form/element/input'
            ],
            'dataScope' => $scope . '.' . $attributeCode,
            'sortOrder' => '96',
            'visible' => true,
            'provider' => 'checkoutProvider',
            'validation' => [],
            'options' => [],
            'label' => __('County'),
            'value' => ''
        ];

        return $field;
    }

    public function getAdditionalFields($addressType = 'shipping')
    {
        $shippingAttributes = [];
        $billingAttributes = [];
        $shippingAttributes[] = 'county';
        $billingAttributes[] = 'county';

        return $addressType == 'shipping' ? $shippingAttributes : $billingAttributes;
    }
}
