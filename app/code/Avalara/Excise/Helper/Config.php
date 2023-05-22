<?php

namespace Avalara\Excise\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\ProductMetadataInterface;
use Avalara\Excise\Framework\Constants;
use Magento\Backend\Model\UrlInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * Avalara Config model
 */
class Config extends AbstractHelper
{
    const XML_PATH_AVALARA_MODULE_ENABLED = "tax/avatax_excise/enabled";

    const XML_PATH_AVALARA_MODE = "tax/avatax_excise/mode";

    const XML_PATH_EXCISE_ACCOUNT_NUMBER = "tax/avatax_excise/excise_account_number";

    const XML_PATH_EXCISE_LICENSE_KEY = "tax/avatax_excise/excise_license_key";

    const XML_PATH_AVALARA_ADVANCED_API_TIMEOUT = "tax/avatax_excise/api_timeout";

    const XML_PATH_AVATAX_ACCOUNT_NUMBER = "tax/avatax_excise/avatax_account_number";

    const XML_PATH_AVATAX_LICENSE_KEY = "tax/avatax_excise/avatax_license_key";

    const XML_PATH_AVATAX_TAX_CALCULATION_COUNTRIES_ENABLED = 'tax/avatax_excise/countries_enable';

    const XML_PATH_AVATAX_EXCISE_COMPANY_ID = 'tax/avatax_excise/excise_company';

    const XML_PATH_AVATAX_COMPANY_ID = 'tax/avatax_excise/avatax_company';

    const XML_PATH_AVALARA_ADDRESS_VALIDATION_ENABLED = "tax/avatax/address_validation_enabled";

    const XML_PATH_AVALARA_ADDRESS_VALIDATION_ERROR_INSTRUCTIONS = "tax/avatax/address_validation_error_instruction";

    const XML_PATH_AVALARA_ADDRESS_VALIDATION_COUNTRIES_ENABLED = "tax/avatax/address_validation_countries";

    const XML_PATH_AVALARA_BILLING_ADDRESS_VALIDATION_ENABLED = "tax/avatax/billing_address_validation_enabled";

    const XML_PATH_ADDRESS_VALIDATION_ENABLED = 'tax/avatax/address_validation_enabled';

    const XML_PATH_ADDRESS_VALIDATION_METHOD = 'tax/avatax/address_validation_choose_address';

    const XML_PATH_ADDRESS_VALIDATION_INSTRUCTIONS_WITH_CHOICE
    = "tax/avatax/address_validation_instructions_with_choice";

    const XML_PATH_ADDRESS_VALIDATION_INSTRUCTIONS_WITHOUT_CHOICE
    = "tax/avatax/address_validation_instructions_without_choice";

    const XML_PATH_ADDRESS_VALIDATION_ERROR_INSTRUCTION = 'tax/avatax/address_validation_error_instruction';

    const XML_PATH_ADDRESS_VALIDATION_COUNTRIES_ENABLED = 'tax/avatax/address_validation_countries';

    const TAX_DEFAULT_LOGGER = 'tax.log';
    /**
     * Cache tag code
     */
    const AVALARA_CACHE_TAG = 'AVALARA';

    const XML_PATH_AVATAX_TAX_MODE = 'tax/avatax_excise/tax_mode';

    const XML_PATH_STORE_ORIGIN_COUNTRY_ID = 'general/store_information/country_id';

    const XML_PATH_STORE_ORIGIN_ADDRESS1 = 'general/store_information/street_line1';

    const XML_PATH_STORE_ORIGIN_ADDRESS2 = 'general/store_information/street_line2';

    const XML_PATH_STORE_ORIGIN_CITY = 'general/store_information/city';

    const XML_PATH_STORE_ORIGIN_REGION_ID = 'general/store_information/region_id';

    const XML_PATH_STORE_ORIGIN_POSTCODE = 'general/store_information/postcode';

    const XML_PATH_STORE_SALE_COUNTRY_ID = 'shipping/origin/country_id';

    const XML_PATH_AVATAX_EXCISE_LOG_ENABLED = 'tax/avatax_excise/logging_enabled';

    const XML_PATH_AVATAX_EXCISE_LOG_MODE = 'tax/avatax_excise/logging_mode';

    const XML_PATH_TAX_CALCULATION_PRICE_INCLUDES_TAX = 'tax/calculation/price_includes_tax';

    const XML_PATH_TAX_CALCULATION_TAX_MODE = 'tax/avatax_excise/tax_mode';

    const XML_PATH_TAX_CALCULATION_TAX_COMMIT_STATUS = 'tax/avatax_excise/commit_status';

    const XML_PATH_AVATAX_EXCISE_LOG_LIMIT = 'tax/avatax_excise/logging_limit';

    const XML_PATH_AVATAX_EXCISE_QUEUE_LIMIT = 'tax/avatax_excise/queue_limit';

    const XML_PATH_AVATAX_EXCISE_SHIPPING_CODE = 'tax/avatax_excise/shipping_code';

    const XML_PATH_AVATAX_TAX_INCLUDED = 'tax/cart_display/full_summary';

    const XML_SUFFIX_AVATAX_TAX_INCLUDED = "(Shipping Tax Inclusive)";

    /**
     * @var ProductMetadataInterface
     */
    protected $mageMetadata = null;

    /**
     * @var UrlInterface
     */
    protected $backendUrl;

    /**
     * @var EncryptorInterface
     */
    protected $encryptor;

    /**
     * @var TimezoneInterface
     */
    protected $timeZone;

    /**
     * Class constructor
     *
     * @param Context $context
     * @param ProductMetadataInterface $mageMetadata
     * @param UrlInterface $backendUrl
     * @param EncryptorInterface $encryptor
     * @param TimezoneInterface $timeZone
     */
    public function __construct(
        Context $context,
        ProductMetadataInterface $mageMetadata,
        UrlInterface $backendUrl,
        EncryptorInterface $encryptor,
        TimezoneInterface $timeZone
    ) {
        parent::__construct($context);
        $this->mageMetadata = $mageMetadata;
        $this->backendUrl = $backendUrl;
        $this->encryptor = $encryptor;
        $this->timeZone = $timeZone;
    }

    /**
     * Return whether module is enabled
     *
     * @param null $store
     * @param      $scopeType
     *
     * @return mixed
     */
    public function isModuleEnabled($store = null, $scopeType = ScopeInterface::SCOPE_STORE)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_AVALARA_MODULE_ENABLED,
            $scopeType,
            $store
        );
    }

    /**
     * Return whether Address Validation is enabled
     *
     * @param null $store
     * @param string $scopeType
     * @return mixed
     */
    public function isAddressValidationEnabled($store = null, $scopeType = ScopeInterface::SCOPE_STORE)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_ADDRESS_VALIDATION_ENABLED,
            $scopeType,
            $store
        );
    }

    /**
     * Returns if user is allowed to choose between the original address and the validated address
     *
     * @param null $store
     *
     * @return mixed
     */
    public function getAllowUserToChooseAddress($store)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_ADDRESS_VALIDATION_METHOD,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param $store
     * @return string
     */
    public function getAddressValidationInstructionsWithChoice($store)
    {
        $addressInstruction =  strip_tags((string)$this->scopeConfig->getValue(
            self::XML_PATH_ADDRESS_VALIDATION_INSTRUCTIONS_WITH_CHOICE,
            ScopeInterface::SCOPE_STORE,
            $store
        ));
        $addressInstruction .= ' <a href="#" class="edit-address">Click Here</a> to change your address.';
        return $addressInstruction;
    }

    /**
     * @param $store
     * @return string
     */
    public function getExciseTaxMode($store)
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_TAX_CALCULATION_TAX_MODE,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param $store
     * @return string
     */
    public function getExciseTaxCommitStatus($store)
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_TAX_CALCULATION_TAX_COMMIT_STATUS,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param $store
     * @return string
     */
    public function getAddressValidationInstructionsWithoutChoice($store)
    {
        $addressInstructionWithoutChoice = strip_tags((string)$this->scopeConfig->getValue(
            self::XML_PATH_ADDRESS_VALIDATION_INSTRUCTIONS_WITHOUT_CHOICE,
            ScopeInterface::SCOPE_STORE,
            $store
        ));
        $addressInstructionWithoutChoice .= '<a href="#" class="edit-address">Click Here</a> to change your address.';
        return $addressInstructionWithoutChoice;
    }

    /**
     * @param $store
     * @return string
     */
    public function getAddressValidationErrorInstruction($store)
    {
        $addressValidationErrorInstruction = strip_tags((string)$this->scopeConfig->getValue(
            self::XML_PATH_ADDRESS_VALIDATION_ERROR_INSTRUCTION,
            ScopeInterface::SCOPE_STORE,
            $store
        ));
        $addressValidationErrorInstruction .= ' <a href="#" class="edit-address">Click Here</a> to change your address.';
        return $addressValidationErrorInstruction;
    }

    /**
     * Returns which countries were enabled to validate the users address
     *
     * @param $store
     *
     * @return mixed
     */
    public function getAddressValidationCountriesEnabled($store)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_ADDRESS_VALIDATION_COUNTRIES_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Get company code from config
     *
     * @param int|null    $store
     * @param string|null $scopeType
     * @param bool|null   $isProduction Get the value for a specific mode instead of relying on the saved value
     *
     * @return int|null
     */
    public function getExciseCompanyId($store = null, $scopeType = ScopeInterface::SCOPE_STORE)
    {
        $companyId = $this->scopeConfig->getValue(
            self::XML_PATH_AVATAX_EXCISE_COMPANY_ID,
            $scopeType,
            $store
        );

        if ($companyId !== null) {
            $companyId = (int)$companyId;
        }

        return $companyId;
    }

    /**
     * Get company code from config
     *
     * @param int|null    $store
     * @param string|null $scopeType
     * @param bool|null   $isProduction Get the value for a specific mode instead of relying on the saved value
     *
     * @return int|null
     */
    public function getAvataxCompanyId($store = null, $scopeType = ScopeInterface::SCOPE_STORE)
    {
        $companyId = $this->scopeConfig->getValue(
            self::XML_PATH_AVATAX_COMPANY_ID,
            $scopeType,
            $store
        );

        if ($companyId !== null) {
            $companyId = (int)$companyId;
        }

        return $companyId;
    }

    /**
     * Get current mode of the module
     *
     * @param int|null    $store
     * @param string|null $scopeType
     *
     * @return bool
     */
    public function isProductionMode($store = null, $scopeType = ScopeInterface::SCOPE_STORE)
    {
        return !(bool)$this->scopeConfig->getValue(
            self::XML_PATH_AVALARA_MODE,
            $scopeType,
            $store
        );
    }

    /**
     * @param $store
     * @param $scopeType
     *
     * @return mixed
     */
    public function getTaxCalculationCountriesEnabled($store, $scopeType = ScopeInterface::SCOPE_STORE)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_AVATAX_TAX_CALCULATION_COUNTRIES_ENABLED,
            $scopeType,
            $store
        );
    }

    /**
     * Get current mode in string format of the module
     *
     * @param int|null    $store
     * @param string|null $scopeType
     *
     * @return String
     */
    public function getCurrentModeString($store = null, $scopeType = ScopeInterface::SCOPE_STORE)
    {
        $isProduction = $this->isProductionMode($store, $scopeType);
        return $isProduction ? Constants::API_MODE_PROD : Constants::API_MODE_DEV;
    }

    /**
     * Get account number from config
     *
     * @param int|null    $store
     * @param string|null $scopeType
     *
     * @return string
     */
    public function getExciseAccountNumber($store = null, $scopeType = ScopeInterface::SCOPE_STORE)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_EXCISE_ACCOUNT_NUMBER,
            $scopeType,
            $store
        );
    }

    /**
     * Get license key from config
     *
     * @param int|null    $store
     * @param string|null $scopeType
     *
     * @return string
     */
    public function getExciseLicenseKey($store = null, $scopeType = ScopeInterface::SCOPE_STORE)
    {
        return $this->encryptor->decrypt(
            $this->scopeConfig->getValue(
                self::XML_PATH_EXCISE_LICENSE_KEY,
                $scopeType,
                $store
            )
        );
    }

    /**
     * Return the timeout for using the Avalara API
     *
     * @param null $store
     * @param      $scopeType
     *
     * @return float
     */
    public function getAvalaraApiTimeout($store = null, $scopeType = ScopeInterface::SCOPE_STORE)
    {
        return (float)$this->scopeConfig->getValue(
            self::XML_PATH_AVALARA_ADVANCED_API_TIMEOUT,
            $scopeType,
            $store
        );
    }

    /**
     * Get account number from config
     *
     * @param int|null    $store
     * @param string|null $scopeType
     *
     * @return string
     */
    public function getAvaTaxAccountNumber($store = null, $scopeType = ScopeInterface::SCOPE_STORE)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_AVATAX_ACCOUNT_NUMBER,
            $scopeType,
            $store
        );
    }

    /**
     * Get license key from config
     *
     * @param int|null    $store
     * @param string|null $scopeType
     *
     * @return string
     */
    public function getAvaTaxLicenseKey($store = null, $scopeType = ScopeInterface::SCOPE_STORE)
    {
        return $this->encryptor->decrypt(
            $this->scopeConfig->getValue(
                self::XML_PATH_AVATAX_LICENSE_KEY,
                $scopeType,
                $store
            )
        );
    }

    /**
     * Return tax mode
     *
     * @param int|null    $store
     * @param string|null $scopeType
     *
     * @return mixed
     */
    public function getTaxMode($store = null, $scopeType = ScopeInterface::SCOPE_STORE)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_AVATAX_TAX_MODE,
            $scopeType,
            $store
        );
    }

    /**
     * Generate Avalara Application Name from a combination of Magento version number and Avalara module name
     * Format: Magento 2.x Community - Avalara
     * Limited to 50 characters to comply with API requirements
     *
     * @return string
     */
    public function getApplicationName()
    {
        return substr($this->mageMetadata->getName(), 0, 7) . ' ' . // "Magento" - 8 chars
            substr(
                $this->mageMetadata->getVersion(),
                0,
                14
            ) . ' ' . // 2.x & " " - 50 - 8 - 13 - 14 = 15 chars
            substr(
                $this->mageMetadata->getEdition(),
                0,
                10
            ) . ' - ' . // "Community - "|"Enterprise - " - 13 chars
            'Avalara';
    }

    /**
     * The version of the Avalara module
     *
     * @return string
     */
    public function getApplicationVersion()
    {
        return Constants::APP_VERSION;
    }

    /**
     * Get the base URL minus protocol and trailing slash, for use as machine name in API requests
     *
     * @return string
     */
    public function getApplicationDomain()
    {
        $domain = $this->backendUrl->getBaseUrl();
        $domain = preg_replace('#^https?://#', '', $domain);
        return preg_replace('#/$#', '', $domain);
    }
    /**
     * Return origin address
     *
     * @return array
     */
    public function getOriginAddress($store)
    {
        $data = [
            'OriginAddress1' => $this->scopeConfig->getValue(
                self::XML_PATH_STORE_ORIGIN_ADDRESS1,
                ScopeInterface::SCOPE_STORE,
                $store
            ),
            'OriginAddress2' => $this->scopeConfig->getValue(
                self::XML_PATH_STORE_ORIGIN_ADDRESS2,
                ScopeInterface::SCOPE_STORE,
                $store
            ),
            'OriginCity' => $this->scopeConfig->getValue(
                self::XML_PATH_STORE_ORIGIN_CITY,
                ScopeInterface::SCOPE_STORE,
                $store
            ),
            'OriginJurisdiction' => $this->scopeConfig->getValue(
                self::XML_PATH_STORE_ORIGIN_REGION_ID,
                ScopeInterface::SCOPE_STORE,
                $store
            ),
            'OriginPostalCode' => $this->scopeConfig->getValue(
                self::XML_PATH_STORE_ORIGIN_POSTCODE,
                ScopeInterface::SCOPE_STORE,
                $store
            ),
            'OriginCountryCode' => $this->scopeConfig->getValue(
                self::XML_PATH_STORE_ORIGIN_COUNTRY_ID,
                ScopeInterface::SCOPE_STORE,
                $store
            ),
            'OriginCounty' => '',
            'OriginType' => '',
            'Origin' => '',
            'OriginOutCityLimitInd' => '',
            'OriginSpecialJurisdictionInd' => '',
            'OriginExciseWarehouse' => '',
            'OriginAirportCode' => ''
        ];

        return $data;
    }

    /**
     * Return if address validation is activated
     *
     * @param null $store
     *
     * @return mixed
     */
    public function isAddressValidationActivated($store)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_AVALARA_ADDRESS_VALIDATION_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Instructions if there was an error in validating their address
     *
     * @param $store
     *
     * @return string
     */
    public function getAddressValidationErrorInstructions($store)
    {
        $addressErrorInstruction =  strip_tags((string)$this->scopeConfig->getValue(
            self::XML_PATH_AVALARA_ADDRESS_VALIDATION_ERROR_INSTRUCTIONS,
            ScopeInterface::SCOPE_STORE,
            $store
        ));

        $addressErrorInstruction .= ' <a href="#" class="edit-address">Click Here</a> to change your address.';
        return $addressErrorInstruction;
    }

    /**
     * Returns which countries  enabled to validate the users address
     *
     * @param $store
     *
     * @return mixed
     */
    public function getCountriesEnabledForAddressValidation($store)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_AVALARA_ADDRESS_VALIDATION_COUNTRIES_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Determine whether address is taxable, based on either country
     *
     * @param \Magento\Framework\DataObject $address
     * @param                               $storeId
     * @codeCoverageIgnore
     * @return bool
     */
    public function isAddressTaxable(\Magento\Framework\DataObject $address, $storeId)
    {
        $isTaxable = true;
        $countryFilters = explode(',', (string)$this->getCountriesEnabledForAddressValidation($storeId));
        $countryId = $address->getCountryId();
        if (!in_array($countryId, $countryFilters)) {
            $isTaxable = false;
        }

        return $isTaxable;
    }

    /**
     * Return if billing address validation is enabled
     *
     * @param null $store
     *
     * @return mixed
     */
    public function isBillingAddressValidationEnabled($store)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_AVALARA_BILLING_ADDRESS_VALIDATION_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param $store
     * @return string
     */
    public function getPriceIncludesTax($store)
    {
        $val = $this->scopeConfig->getValue(
            self::XML_PATH_TAX_CALCULATION_PRICE_INCLUDES_TAX,
            ScopeInterface::SCOPE_STORE,
            $store
        );
        if ($val == 0) {
            return "false";
        } else {
            return "true";
        }
    }
    /**
     * Return configured log level
     *
     * @return int
     */
    public function getLogEnabled()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_AVATAX_EXCISE_LOG_ENABLED);
    }

    /**
     * Return configured log detail
     *
     * @return int
     */
    public function getLogMode()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_AVATAX_EXCISE_LOG_MODE);
    }

    /**
     * Returns current store time zone object
     *
     * @return TimezoneInterface
     */
    public function getTimeZoneObject()
    {
        return $this->timeZone;
    }

    /**
     * Return configured log limit
     *
     * @return int
     */
    public function getLogLimit()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_AVATAX_EXCISE_LOG_LIMIT);
    }

    /**
     * Return configured queue limit
     *
     * @return int
     */
    public function getQueueLimit()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_AVATAX_EXCISE_QUEUE_LIMIT);
    }

    /**
     * Return configured shipping code
     *
     * @return string
     */
    public function getShippingCode()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_AVATAX_EXCISE_SHIPPING_CODE);
    }

    /**
     * Get Tax summary config
     *
     * @param null $store
     * @return boolean
     */
    public function getTaxSummaryConfig($store = null)
    {
        return (boolean)$this->scopeConfig->getValue(
            self::XML_PATH_AVATAX_TAX_INCLUDED,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }
}
