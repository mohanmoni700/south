<?php
/**
 * @author  CORRA
 */

namespace Corra\Veratad\Model;

use Exception;
use Magento\Framework\HTTP\ZendClient;
use Magento\Framework\HTTP\ZendClientFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Psr\Log\LoggerInterface;

/**
 * Handles Verification via Veratad API
 */
class VeratadApi
{
    protected const VERATAD_ENABLED = 'veratad_settings/general/enabled';
    protected const VERATAD_USER_NAME = 'veratad_settings/agematch/username';
    protected const VERATAD_PASSWORD = 'veratad_settings/agematch/password';
    protected const AGE_MATCH_SERVICE = 'veratad_settings/agematch/agematchservice';
    protected const AGE_MATCH_RULE_DEFAULT = 'veratad_settings/agematch/agematchrules';

    protected const VERATAD_API_ENDPOINT = 'veratad_settings/agematch/url';
    protected const VERATAD_TEST_MODE = 'veratad_settings/general/test_mode';
    protected const VERATAD_TEST_KEY = 'veratad_settings/general/test_key';
    protected const VERATAD_GLOBAL_AGE = 'veratad_settings/global/global_age';

    protected const AGEMATCH_SUCCESS_MSG = 'veratad_settings/content/agematch_success_subtitle';
    protected const AGEMATCH_FAILURE_MSG = 'veratad_settings/content/agematch_fail_subtitle';
    protected const CONTENT_TYPE = 'application/json';
    protected const API_RESPONSE_SUCCESS = "PASS";
    protected const SAVE_AGE_VERIFICATION_ORDER = 'veratad_settings/data_save/save_age_verification_order';
    protected const SAVE_AGE_VERIFICATION_CUSTOMER = 'veratad_settings/data_save/save_age_verification_customer';
    protected const AGE_MATCH_RULE_PHONE = 'veratad_settings/agematch/agematchrule_phone';

    /**
     * @var ZendClientFactory
     */
    protected $httpClientFactory;

    /**
     * @var array
     */
    protected $baseParams;

    /**
     * @var Json
     */
    protected $json;
    /**
     * @var TimezoneInterface
     */
    protected $date;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * VeratadApi Constructor
     *
     * @param ZendClientFactory $httpClientFactory
     * @param Json $json
     * @param TimezoneInterface $date
     * @param ScopeConfigInterface $scopeConfig
     * @param LoggerInterface $logger
     */
    public function __construct(
        ZendClientFactory $httpClientFactory,
        Json $json,
        TimezoneInterface $date,
        ScopeConfigInterface $scopeConfig,
        LoggerInterface $logger
    ) {
        $this->httpClientFactory = $httpClientFactory;
        $this->json = $json;
        $this->date = $date;
        $this->scopeConfig = $scopeConfig;
        $this->logger = $logger;
    }

    /**
     * Getting the storeConfigValues
     *
     * @param string $key
     * @param null|int $storeId
     * @return mixed
     */
    public function getKey($key, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            $key,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Returns whether the feature is enabled or not.
     *
     * @return bool
     */
    public function isEnabled()
    {
        return (bool) $this->getKey(self::VERATAD_ENABLED);
    }

    /**
     * Returns whether the Ageverification Order Save is enabled or not.
     *
     * @return bool
     */
    public function saveAgeVerificationOrder()
    {
        return (bool) $this->getKey(self::SAVE_AGE_VERIFICATION_ORDER);
    }

    /**
     * Returns whether the Ageverification Customer Save is enabled or not.
     *
     * @return bool
     */
    public function saveAgeVerificationCustomer()
    {
        return (bool) $this->getKey(self::SAVE_AGE_VERIFICATION_CUSTOMER);
    }

    /**
     * Get Success Message Info
     *
     * @return mixed
     */
    public function getSuccessMessageInfo()
    {
        return $this->getKey(self::AGEMATCH_SUCCESS_MSG);
    }

    /**
     * Get AgeMatchRule Phone
     *
     * @return mixed
     */
    public function getAgeMatchRulePhone()
    {
        return $this->getKey(self::AGE_MATCH_RULE_PHONE);
    }

    /**
     * Get Failurre Message Info
     *
     * @return mixed
     */
    public function getFailureMessageInfo()
    {
        return $this->getKey(self::AGEMATCH_FAILURE_MSG);
    }

    /**
     * Billing and Shipping Name checking same or not
     *
     * @param array $target
     * @param array $shipping
     * @return bool
     */
    public function nameDetection($target, $shipping)
    {
        $targetFirstname = $target['firstname'];
        $targetLastname = $target['lastname'];
        $shippingFirstname = $shipping['firstname'];
        $shippingLastname = $shipping['lastname'];

        $targetName = strtolower($targetFirstname . $targetLastname);
        $shippingName = strtolower($shippingFirstname . $shippingLastname);

        return ($targetName === $shippingName);
    }

    /**
     * DOB condition check
     *
     * @param array $postParams
     * @param string $dateOfBirth
     * @return mixed|string
     */
    protected function dobConditionCheck($postParams, $dateOfBirth)
    {
        $dob = "";
        if (!empty($postParams['dob']) && isset($postParams['dob'])) {
            $dateOfBirth = $postParams['dob'];
        }
        if ($dateOfBirth) {
            $yob = substr($dateOfBirth, 0, 4);
            $currentYear = $this->date->date()->format('Y');
            if ($yob >= 1900 && $yob < $currentYear) {
                $dob = $dateOfBirth;
            }
        }
        return $dob;
    }

    /**
     * Get parameters used in all API calls
     *
     * @param string $rule
     * @return array
     */
    protected function getBaseParams($rule = "")
    {
        $this->baseParams = [
            'user' => $this->getKey(self::VERATAD_USER_NAME),
            'pass' => $this->getKey(self::VERATAD_PASSWORD),
            'service' => $this->getKey(self::AGE_MATCH_SERVICE),
            'rules' => !empty($rule)?$rule:$this->getKey(self::AGE_MATCH_RULE_DEFAULT)
        ];
        return $this->baseParams;
    }

    /**
     *  Get the HttpClient
     *
     * @return ZendClient
     */
    protected function getClient()
    {
        return $this->httpClientFactory->create();
    }

    /**
     * Get TargetParams used in API calls
     *
     * @param array $postParams
     * @param string $dob
     * @param string $rule
     * @return array
     */
    protected function getTargetParams($postParams, $dob, $rule)
    {
        $testMode = $this->getKey(self::VERATAD_TEST_MODE);
        $testKey = null;
        if ($testMode) {
            $testKey = $this->getKey(self::VERATAD_TEST_KEY);
        }
        $addrClean = str_replace("\n", ' ', $postParams['street']);
        $age = $this->getKey(self::VERATAD_GLOBAL_AGE);
        if (!$age) {
            $age = "21+";
        }
        return [
            "test_key" => $testKey,
            "fn" => $postParams['firstname'],
            "ln" => $postParams['lastname'],
            "addr" => $addrClean,
            "city" => (!empty($postParams['city'])?$postParams['city']:''),
            "state" => (!empty($postParams['region'])?$postParams['region']:''),
            "zip" => $postParams['postcode'],
            "age" => $age,
            "dob" => empty($rule)?$dob:'',
            "phone" => (!empty($postParams['telephone'])?$postParams['telephone']:''),
            "email" => (!empty($postParams['email'])?$postParams['email']:'')
        ];
    }

    /**
     * Make POST requests
     *
     * @param array $postParams
     * @param string $dob
     * @param string $rule
     * @return bool
     */
    public function veratadPost($postParams, $dob = "", $rule = "")
    {
        $response = false;
        $enabled = $this->getKey(self::VERATAD_ENABLED);
        if ($enabled && $postParams) {
            $endpoint = trim($this->getKey(self::VERATAD_API_ENDPOINT));
            $params = $this->getBaseParams($rule);
            $params['reference'] = (!empty($postParams['email'])?$postParams['email']:'');
            $dob = $this->dobConditionCheck($postParams, $dob);
            $params['target'] = $this->getTargetParams($postParams, $dob, $rule);
            $dataString = $this->json->serialize($params);
            $params['pass'] = "xxxx";
            $logQuery = $this->json->serialize($params);
            $this->logger->info('veratad query:' . $logQuery);
            try {
                $client = $this->getClient();
                $client->setUri($endpoint);
                $client->setHeaders(
                    [
                        'Content-type' => self::CONTENT_TYPE,
                        'Accept' => self::CONTENT_TYPE
                    ]
                );
                $client->setRawData($dataString, self::CONTENT_TYPE);
                $apiResponse = $client->request('POST');
                $rawResponse = $apiResponse->getRawBody();
                $responseDecoded = $this->json->unserialize($rawResponse);
                $response = ($responseDecoded['result']['action'] === self::API_RESPONSE_SUCCESS);
                $this->logger->info('veratad response:' . $rawResponse);
            } catch (Exception $e) {
                $this->logger->critical($e->getMessage());
            }
        }
        return $response;
    }
}
