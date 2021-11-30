<?php
/**
 * @author  CORRA
 */

namespace HookahShisha\Veratad\Helper;

use Exception;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\HTTP\ZendClient;
use Magento\Framework\HTTP\ZendClientFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * Handles Verification via Veratad API
 */
class Api extends AbstractHelper
{
    protected const CONTENT_TYPE = 'application/json';

    /**
     * @var ZendClientFactory
     */
    protected $httpClientFactory;
    /**
     * @var array
     */
    private $baseParams;

    /**
     * @var Json
     */
    protected $json;
    /**
     * @var TimezoneInterface
     */
    protected $date;

    /**
     * Api constructor.
     * @param Context $context
     * @param ZendClientFactory $httpClientFactory
     * @param Json $json
     * @param TimezoneInterface $date
     */
    public function __construct(
        Context $context,
        ZendClientFactory $httpClientFactory,
        Json $json,
        TimezoneInterface $date
    ) {
        $this->httpClientFactory = $httpClientFactory;
        $this->json = $json;
        $this->date = $date;
        parent::__construct($context);
    }

    /**
     * Check the Customer Group is in Exclude list or not
     *
     * @param int $customer_group_id
     * @return bool
     */
    public function isExcludedCustomerGroup($customer_group_id)
    {
        if ($customer_group_id) {
            $groups_excluded = $this->getKey('veratad_settings/customer_groups_veratad/customer_group_list_veratad');
            $result = (strpos($groups_excluded, $customer_group_id) !== false);
        } else {
            $result = false;
        }
        return $result;
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
     * Billing and Shipping Name checking same or not
     *
     * @param array $target
     * @param array $shipping
     * @return bool
     */
    public function nameDetection($target, $shipping)
    {
        $target_firstname = $target['firstname'];
        $target_lastname = $target['lastname'];
        $shipping_firstname = $shipping['firstname'];
        $shipping_lastname = $shipping['lastname'];

        $targetName = strtolower($target_firstname . $target_lastname);
        $shippingName = strtolower($shipping_firstname . $shipping_lastname);

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
        if (array_key_exists('dob', $postParams)) {
            $date_of_birth = $postParams['dob'];
        } else {
            $date_of_birth = $dateOfBirth;
        }
        if ($date_of_birth) {
            $yob = substr($date_of_birth, 0, 4);
            $current_year = $this->date->date()->format('Y');
            if ($yob >= 1900 && $yob < $current_year) {
                $dob = $date_of_birth;
            }
        }
        return $dob;
    }

    /**
     * SSN Data check
     *
     * @param array $postParams
     * @return mixed|string
     */
    protected function ssnDataCheck($postParams)
    {
        $ssn = "";
        if (array_key_exists('ssn', $postParams)) {
            $ssn = $postParams['ssn'];
        }
        return $ssn;
    }

    /**
     * Make POST requests
     *
     * @param array $postParams
     * @param string $dob
     * @return mixed
     */
    public function veratadPost($postParams, $dob = "")
    {
        $enabled = $this->getKey('veratad_settings/general/enabled');
        if ($enabled && $postParams) {
            $endpoint = trim($this->getKey('veratad_settings/agematch/url'));
            $params = $this->getBaseParams();
            $params['reference'] = $postParams['email'];

            $test_mode = $this->getKey('veratad_settings/general/test_mode');
            $test_key = null;
            if ($test_mode) {
                $test_key = $this->getKey('veratad_settings/general/test_key');
            }
            $addr_clean = str_replace("\n", ' ', $postParams['street']);
            $dob = $this->dobConditionCheck($postParams, $dob);
            $ssn = $this->ssnDataCheck($postParams);

            $age = $this->getKey('veratad_settings/global/global_age');
            if (!$age) {
                $age = "21+";
            }

            $params['target'] = [
                "test_key" => $test_key,
                "fn" => $postParams['firstname'],
                "ln" => $postParams['lastname'],
                "addr" => $addr_clean,
                "city" => $postParams['city'],
                "state" => $postParams['region'],
                "zip" => $postParams['postcode'],
                "age" => $age,
                "dob" => $dob,
                "ssn" => $ssn,
                "phone" => $postParams['telephone'],
                "email" => $postParams['email']
            ];
            $data_string = $this->json->serialize($params);
            $params['pass'] = "xxxx";
            $params['target']['ssn'] = "xxxx";
            $log_query = $this->json->serialize($params);
            $this->_logger->info('veratd query:' . $log_query);
            try {
                $client = $this->getClient();
                $client->setUri($endpoint);
                $client->setHeaders(
                    [
                        'Content-type' => self::CONTENT_TYPE,
                        'Accept' => self::CONTENT_TYPE
                    ]
                );
                $client->setRawData($data_string, self::CONTENT_TYPE);
                $apiResponse = $client->request('POST');
                $rawResponse = $apiResponse->getRawBody();
                $response = $this->json->unserialize($rawResponse);
                $this->_logger->info('veratd response:' . $rawResponse);
            } catch (Exception $e) {
                $this->_logger->critical($e->getMessage());
                return false;
            }
            return $response['result'];
        }
    }

    /**
     * Get parameters used in all API calls
     *
     * @return array
     */
    protected function getBaseParams()
    {
        if ($this->baseParams !== null) {
            return $this->baseParams;
        }
        $this->baseParams = [
            'user' => $this->getKey('veratad_settings/agematch/username'),
            'pass' => $this->getKey('veratad_settings/agematch/password'),
            'service' => $this->getKey('veratad_settings/agematch/agematchservice'),
            'rules' => $this->getKey('veratad_settings/agematch/agematchrules')
        ];
        return $this->baseParams;
    }

    /**
     *  Get the HttpClient
     *
     * @return ZendClient
     */
    public function getClient()
    {
        return $this->httpClientFactory->create();
    }
}
