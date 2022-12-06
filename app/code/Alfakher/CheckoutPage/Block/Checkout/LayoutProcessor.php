<?php
declare(strict_types=1);

namespace Alfakher\CheckoutPage\Block\Checkout;

use HookahShisha\InternationalTelephoneInput\Helper\Data;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class LayoutProcessor
{
    public const WEBSITE_CODE = 'hookahshisha/website_code_setting/website_code';
    
    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var storeManager
     */
    protected $storeManager;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * LayoutProcessor constructor.
     *
     * @param Data $helper
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Data $helper,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->helper = $helper;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * AfterProcess
     *
     * @param LayoutProcessor $subject
     * @param array $jsLayout
     * @return array $jsLayout
     */
    public function afterProcess(
        \Magento\Checkout\Block\Checkout\LayoutProcessor $subject,
        array $jsLayout
    ) {
        $storeScope = ScopeInterface::SCOPE_STORE;
        $website_code = $this->storeManager->getWebsite()->getCode();
        $config_website = $this->scopeConfig->getValue(self::WEBSITE_CODE, $storeScope);
        $websidecodes = explode(',', $config_website);

        $validationClass = in_array($website_code, $websidecodes) ? 'validate-alphanum-with-spaces' : 'letters-only';

        /*For shipping address form*/
        $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
        ['children']['shippingAddress']['children']['shipping-address-fieldset']
        ['children']['street']['children'][0]['label'] = __('Address Line 1*');

        $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
        ['children']['shippingAddress']['children']['shipping-address-fieldset']
        ['children']['street']['children'][1]['label'] = __('Address Line 2');

        $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
        ['children']['shippingAddress']['children']['shipping-address-fieldset']
        ['children']['company']['label'] = __('Company Name');

        $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
        ['children']['shippingAddress']['children']['shipping-address-fieldset']
        ['children']['firstname']['validation'] = ['required-entry' => true, $validationClass => true];

        $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
        ['children']['shippingAddress']['children']['shipping-address-fieldset']
        ['children']['lastname']['validation'] = ['required-entry' => true, $validationClass => true];
        /* Start 25April Country code Adding from the  HookahShisha_InternationalTelephoneInput extension */
        if (isset($jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
            ['children']['shippingAddress']['children']['shipping-address-fieldset']['children'])) {
            $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
            ['children']['shippingAddress']['children']['shipping-address-fieldset']
            ['children']['telephone']['validation'] = ['required-entry' => true];
            $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
            ['children']['shippingAddress']['children']['shipping-address-fieldset']['children']
            ['telephone'] = $this->helper->telephoneFieldConfig("shippingAddress");
        }
        /* End 25April */

        /*For billing address form change lable*/
        /* config: checkout/options/display_billing_address_on = payment_method */
        if (isset($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
            ['payment']['children']['payments-list']['children'])) {

            foreach ($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
                ['payment']['children']['payments-list']['children'] as $key => $payment) {

                if (isset($payment['children']['form-fields']['children']['street'])) {

                    $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
                    ['payment']['children']['payments-list']['children'][$key]
                    ['children']['form-fields']['children']['street']['children'][0]['label'] = __('Address Line 1*');

                    $jsLayout['components']['checkout']['children']['steps']['children']
                    ['billing-step']['children']
                    ['payment']['children']['payments-list']['children'][$key]['children']
                    ['form-fields']['children']['street']['children'][1]['label'] = __('Address Line 2');

                }
                if (isset($payment['children']['form-fields']['children']['company'])) {

                    $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
                    ['payment']['children']['payments-list']['children'][$key]
                    ['children']['form-fields']['children']['company']['label'] = __('Company Name');
                }

                /*For billing address Validation*/
                if (isset($payment['children']['form-fields']['children']['firstname'])) {
                    $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
                    ['payment']['children']['payments-list']['children'][$key]['children']['form-fields']['children']
                    ['firstname']['validation'] = ['required-entry' => true, $validationClass => true];
                }
                if (isset($payment['children']['form-fields']['children']['lastname'])) {
                    $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
                    ['payment']['children']['payments-list']['children'][$key]['children']['form-fields']['children']
                    ['lastname']['validation'] = ['required-entry' => true, $validationClass => true];
                }

                if (isset($payment['children']['form-fields']['children']['telephone'])) {
                    $customScope = $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']
                        ['children']['payment']['children']['payments-list']['children'][$key]
                        ['children']['form-fields']['children']['telephone']['config']['customScope'];

                    $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
                    ['payment']['children']['payments-list']['children'][$key]
                    ['children']['form-fields']['children']['telephone']['config'] =
                    $this->helper->telephoneFieldConfigBilling("billingAddress");

                    $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
                    ['payment']['children']['payments-list']['children'][$key]
                    ['children']['form-fields']['children']['telephone']['config']['customScope'] = $customScope;

                    $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
                    ['payment']['children']['payments-list']['children'][$key]['children']['form-fields']['children']
                    ['telephone']['validation'] = ['required-entry' => true];

                    $jsLayout['components']['checkout']['children']['steps']['children']
                    ['billing-step']['children']['payment']['children']
                    ['payments-list']['children'][$key]['children']
                    ['form-fields']['children']['telephone']['sortOrder'] = 50;
                }

                if (isset($payment['children']['form-fields']['children']['country_id'])) {

                    $jsLayout['components']['checkout']['children']['steps']['children']
                    ['billing-step']['children']['payment']['children']
                    ['payments-list']['children'][$key]['children']
                    ['form-fields']['children']['country_id']['sortOrder'] = 70;

                }

                if (isset($payment['children']['form-fields']['children']['city'])) {

                    $jsLayout['components']['checkout']['children']['steps']['children']
                    ['billing-step']['children']['payment']['children']
                    ['payments-list']['children'][$key]['children']
                    ['form-fields']['children']['city']['sortOrder'] = 80;
                }

                if (isset($payment['children']['form-fields']['children']['region_id'])) {

                    $jsLayout['components']['checkout']['children']['steps']['children']
                    ['billing-step']['children']['payment']['children']
                    ['payments-list']['children'][$key]['children']
                    ['form-fields']['children']['region_id']['sortOrder'] = 81;

                }

                if (isset($payment['children']['form-fields']['children']['postcode'])) {

                    $jsLayout['components']['checkout']['children']['steps']['children']
                    ['billing-step']['children']['payment']['children']
                    ['payments-list']['children'][$key]['children']
                    ['form-fields']['children']['postcode']['sortOrder'] = 93;
                }

            }
        }

        return $jsLayout;
    }
}
