<?php
declare(strict_types=1);

namespace HookahShisha\InternationalTelephoneInput\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class Data extends AbstractHelper
{

    public const XML_PATH_INTERNATIONAL_TELEPHONE_INPUT_MODULE_ENABLED = 'internationaltelephoneinput/general/enabled';

    public const
    XML_PATH_INTERNATIONAL_TELEPHONE_MULTISELECT_COUNTRIES_ALLOWED = 'internationaltelephoneinput/general/allow';

    public const XML_PATH_PREFERED_COUNTRY = 'general/store_information/country_id';

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->storeManager = $storeManager;
    }

    /**
     * Module Enabled
     *
     * @return mixed
     */
    public function isModuleEnabled()
    {
        return $this->getConfig(self::XML_PATH_INTERNATIONAL_TELEPHONE_INPUT_MODULE_ENABLED);
    }

    /**
     * Allow Countries
     *
     * @return mixed
     */
    public function allowedCountries()
    {
        return $this->getConfig(self::XML_PATH_INTERNATIONAL_TELEPHONE_MULTISELECT_COUNTRIES_ALLOWED);
    }

    /**
     * Prefered Country
     *
     * @return mixed
     */
    public function preferedCountry()
    {
        return $this->getConfig(self::XML_PATH_PREFERED_COUNTRY);
    }

    /**
     * Get configuration
     *
     * @param string $configPath
     * @return mixed
     */
    protected function getConfig($configPath)
    {
        return $this->scopeConfig
            ->getValue($configPath, ScopeInterface::SCOPE_STORE, $this->storeManager->getStore()->getId());
    }

    /**
     * Prepare telephone field config according to the Magento default config
     *
     * @param string $addressType
     * @param string $method
     * @return array
     */
    public function telephoneFieldConfig($addressType, $method = '')
    {
        return [
            'component' => 'Magento_Ui/js/form/element/abstract',
            'config' => [
                'customScope' => $addressType . $method,
                'customEntry' => null,
                'template' => 'ui/form/field',
                'elementTmpl' => 'HookahShisha_InternationalTelephoneInput/form/element/telephone',
                'tooltip' => [
                    'description' => 'For delivery questions.',
                    'tooltipTpl' => 'ui/form/element/helper/tooltip',
                ],
            ],
            'dataScope' => $addressType . $method . '.telephone',
            'dataScopePrefix' => $addressType . $method,
            'label' => __('Phone Number'),
            'provider' => 'checkoutProvider',
            'sortOrder' => 3,
            'validation' => [
                "required-entry" => true,
                "max_text_length" => 15,
                "min_text_length" => 1,
                "custom-validate-telephone" => true
            ],
            'options' => [],
            'filterBy' => null,
            'customEntry' => null,
            'visible' => true,
            'focused' => false,
        ];
    }

    /**
     * Get telephoneFieldConfigBilling
     *
     * @param string $addressType
     * @param string $method
     * @return array
     */
    public function telephoneFieldConfigBilling($addressType, $method = '')
    {
        return [
            'customScope' => $addressType . $method,
            'customEntry' => null,
            'template' => 'ui/form/field',
            'elementTmpl' => 'HookahShisha_InternationalTelephoneInput/form/element/telephone',
            'validation' => [
                "required-entry" => true,
                "max_text_length" => 15,
                "min_text_length" => 1,
                "custom-validate-telephone" => true
            ],
            'tooltip' => [
                'description' => 'For delivery questions.',
                'tooltipTpl' => 'ui/form/element/helper/tooltip',
            ],
        ];
    }
}
