<?php
/**
 * @author  CORRA
 */
namespace Corra\Spreedly\Gateway\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use PayPal\Braintree\Model\StoreConfigResolver;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Payment\Gateway\Config\Config as SourceConfig;
use Magento\Payment\Model\Method\Logger;

/**
 *  Spreedly implementation of Payment Gateway Config.
 */
class Config extends SourceConfig
{
    private const KEY_ACTIVE = 'active';
    private const KEY_ENVIRONMENT = 'environment_key';
    private const KEY_ENVIRONMENT_ACCESS_SECRET = 'environment_access_secret_key';
    private const KEY_AUTHORIZENET_GATEWAY_TOKEN = 'authorizenet_gateway_token';
    private const KEY_PAYEEZY_GATEWAY_TOKEN = 'payeezy_gateway_token';
    private const KEY_TITLE = 'title';
    private const KEY_PAYMENT_ACTION = 'payment_action';
    private const KEY_ALLOWSPECIFIC = 'allowspecific';
    private const KEY_SPECIFICCOUNTRY = 'specificcountry';
    private const KEY_CCTYPES = 'cctypes';
    private const KEY_SERVICE_URL = "service_url";
    private const KEY_TEST_MODE = "test_mode";
    private const KEY_TEST_GATEWAY_TOKEN = "test_gateway_token";
    private const KEY_AUTHORIZENET_GATEWAY_ACTIVE = 'authorizenet_gateway_active';
    private const KEY_AUTHORIZENET_GATEWAY_DISTRIBUTION = 'authorizenet_gateway_distribution';
    private const KEY_PAYEEZY_GATEWAY_ACTIVE = 'payeezy_gateway_active';
    private const KEY_PAYEEZY_GATEWAY_DISTRIBUTION = 'payeezy_gateway_distribution';
    private const KEY_CC_TYPES_SPREEDLY_MAPPER = 'cctypes_spreedly_mapper';
    private const KEY_IS_CRON_ENABLED_REMOVE_REDACTED_SAVEDCC = 'cron_enabled';
    /**
     * @ref https://alfakher.atlassian.net/browse/OOKA-50
     * @configkey "payment/spreedly/*"
     */
    private const KEY_GATEWAY_SPECIFIC_FIELDS_ACTIVE = 'gateway_specific_fields_active';
    private const KEY_GATEWAY_SPECIFIC_FIELDS = 'gateway_specific_fields_json';
    /**
     * @var Json
     */
    private $serializer;
    /**
     * @var Logger
     */
    protected $customLogger;
    /**
     * @var StoreConfigResolver
     */
    private $storeConfigResolver;

    /**
     * Type constructer
     *
     * @param StoreConfigResolver $storeConfigResolver
     * @param ScopeConfigInterface $scopeConfig
     * @param Logger $customLogger
     * @param string $methodCode
     * @param string $pathPattern
     * @param Json $serializer
     */
    public function __construct(
        StoreConfigResolver  $storeConfigResolver,
        ScopeConfigInterface $scopeConfig,
        Logger $customLogger,
        $methodCode = null,
        string $pathPattern = self::DEFAULT_PATH_PATTERN,
        Json $serializer = null
    ) {
        parent::__construct($scopeConfig, $methodCode, $pathPattern);
        $this->customLogger = $customLogger;
        $this->storeConfigResolver = $storeConfigResolver;
        $this->serializer = $serializer ?: ObjectManager::getInstance()
            ->get(Json::class);
    }

    /**
     * Get PaymentMethod Active Status
     *
     * @return bool
     */
    public function isActive()
    {
        return (bool)$this->getValue(self::KEY_ACTIVE);
    }
    /**
     * Get Spreedly Environment Key
     *
     * @return string
     */
    public function getEnvironmentKey()
    {
        return $this->getValue(self::KEY_ENVIRONMENT);
    }
    /**
     * Get Spreedly Environment Secret
     *
     * @return string
     */
    public function getEnvironmentSecretKey()
    {
        return $this->getValue(self::KEY_ENVIRONMENT_ACCESS_SECRET);
    }
    /**
     * Get AuthorizeNet Gateway Token
     *
     * @return string
     */
    public function getAuthorizeNetGatewayToken()
    {
        return $this->getValue(self::KEY_AUTHORIZENET_GATEWAY_TOKEN);
    }
    /**
     * Get Payeezy Gateway Token
     *
     * @return string
     */
    public function getPayeezyGatewayToken()
    {
        return $this->getValue(self::KEY_PAYEEZY_GATEWAY_TOKEN);
    }
    /**
     * Get Payment Method Title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->getValue(self::KEY_TITLE);
    }
    /**
     * Get Payment Action value (Pending /Processing)
     *
     * @return mixed|null
     */
    public function getPaymentAction()
    {
        return $this->getValue(self::KEY_PAYMENT_ACTION);
    }
    /**
     * Get Allow Specific country selection
     *
     * @return mixed|null
     */
    public function getAllowspecific()
    {
        return $this->getValue(self::KEY_ALLOWSPECIFIC);
    }
    /**
     * Get Specific country selection
     *
     * @return mixed|null
     */
    public function getSpecificcountry()
    {
        return $this->getValue(self::KEY_SPECIFICCOUNTRY);
    }
    /**
     * Get CreditCard Types
     *
     * @return mixed|null
     */
    public function getCcTypes()
    {
        return $this->getValue(self::KEY_CCTYPES);
    }
    /**
     * Get API Endpoint Url
     *
     * @return mixed|null
     */
    public function getServiceUrl()
    {
        return $this->getValue(self::KEY_SERVICE_URL);
    }
    /**
     * Get TestMode value from system config
     *
     * @return bool
     */
    public function getTestMode()
    {
        return (bool)$this->getValue(self::KEY_TEST_MODE);
    }
    /**
     * Get TestGatewayToken value from system config
     *
     * @return string
     */
    public function getTestGatewayToken()
    {
        return $this->getValue(self::KEY_TEST_GATEWAY_TOKEN);
    }

    /**
     * Get Authorizenet Gateway Active or not
     *
     * @return mixed|null
     */
    public function getAuthorizenetGatewayActive()
    {
        return $this->getValue(self::KEY_AUTHORIZENET_GATEWAY_ACTIVE);
    }

    /**
     * Get Payeezy Gateway Active or not
     *
     * @return mixed|null
     */
    public function getPayeezyGatewayActive()
    {
        return $this->getValue(self::KEY_PAYEEZY_GATEWAY_ACTIVE);
    }
    /**
     * Get Authorizenet Gateway Distribution
     *
     * @return mixed|null
     */
    public function getAuthorizenetGatewayDistribution()
    {
        return $this->getValue(self::KEY_AUTHORIZENET_GATEWAY_DISTRIBUTION);
    }

    /**
     * Get Payeezy Gateway Distribution
     *
     * @return mixed|null
     */
    public function getPayeezyGatewayDistribution()
    {
        return $this->getValue(self::KEY_PAYEEZY_GATEWAY_DISTRIBUTION);
    }

    /**
     * Get configurations to enable/disable redacted CC cronjob
     *
     * @return mixed|null
     */
    public function isRemoveCCRedactedCronEnabled()
    {
        return $this->getValue(self::KEY_IS_CRON_ENABLED_REMOVE_REDACTED_SAVEDCC);
    }

    /**
     * Retrieve mapper between Magento and Spreedly card types
     *
     * @return array
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function getCcTypesMapper(): array
    {
        $result = $this->serializer->unserialize(
            $this->getValue(
                self::KEY_CC_TYPES_SPREEDLY_MAPPER,
                $this->storeConfigResolver->getStoreId()
            )
        );

        return is_array($result) ? $result : [];
    }

    /**
     * Get "GATEWAY_SPECIFIC_FIELDS" related configurations from Magento.
     * Checks the scope of active flag for store/website.
     * Validating the input format, if NOT JSON format @returns false
     *
     * @ref https://alfakher.atlassian.net/browse/OOKA-50
     * @return bool|array
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function getGatewaySpecificFieldsJsonData()
    {
        /** @var $gatewayFieldsActive - GET 'payment/spreedly/gateway_specific_fields_active' **/
        $gatewayFieldsActive = $this->getValue(
            self::KEY_GATEWAY_SPECIFIC_FIELDS_ACTIVE,
            $this->storeConfigResolver->getStoreId()
        );

        /**
         * In admin configurations 'gateway_specific_fields_active' is active
         * If disabled @returns false
         */
        if (!empty($gatewayFieldsActive)) {
            /**
             * Get 'gateway_specific_fields_json' data from admin configurations
             * If disabled @returns false
             */
            $gatewayFieldsJsonData =$this->getValue(
                self::KEY_GATEWAY_SPECIFIC_FIELDS,
                $this->storeConfigResolver->getStoreId()
            );
            /**
             * Checking if correct JSON was updated in admin configurations
             */
            try {
                /** @returns array **/
                return $this->serializer->unserialize($gatewayFieldsJsonData);
            } catch (\Exception $e) {
                $this->customLogger->debug(
                    (array)'Warning: JSON error on spreedly configuration for gateway_specific_fields. '
                );
                return false;
            }
        }
        return false;
    }
}
