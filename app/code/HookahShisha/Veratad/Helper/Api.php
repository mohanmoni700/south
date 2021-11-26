<?php

namespace HookahShisha\Veratad\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\HTTP\ZendClient;
use Magento\Framework\HTTP\ZendClientFactory;

/**
 * Handles Verification via Veratad API
 */
class Api extends \Magento\Framework\App\Helper\AbstractHelper
{
    private $baseParams;
    /**
     * @var ZendClientFactory
     */
    protected $httpClientFactory;

    /**
     * Oauth constructor.
     * @param Context $context
     * @param ZendClientFactory $httpClientFactory
     */
    public function __construct(
        Context $context,
        ZendClientFactory $httpClientFactory
    ) {
        $this->httpClientFactory = $httpClientFactory;
        parent::__construct($context);
    }

    /**
     * @return ZendClient
     */
    public function getClient()
    {
        return $this->httpClientFactory->create();
    }

    /**
     * @param $key
     * @param null $storeId
     * @return mixed
     */
    public function getKey($key, $storeId = null)
    {
        return $this->scopeConfig->getValue(
             $key,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }


    /**
     * Get parameters used in all API calls
     * return array
     */
    private function getBaseParams()
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
     * Make POST requests
     * @param $params
     * @return bool|mixed
     */
    public function apiPost($postParams)
    {
        $response = [];
        $endpoint = trim($this->getKey('veratad_settings/agematch/url'));
        $params = $this->getBaseParams();
        $params['reference'] = $postParams['email'];

        $test_mode = $this->getKey('veratad_settings/general/test_mode');
        $test_key = null;
        if($test_mode){
            $test_key = $this->getKey('veratad_settings/general/test_key');
        }
        $addr_clean = str_replace("\n", ' ', $postParams['street']);
        $dob =$postParams['dob'];
       /* $yob = substr($dob, 0, 4);
        $current_year = $this->date->date()->format('Y');

        if ($yob >= 1900 && $yob < $current_year){
            $dob = $dob;
        }else{
            $dob = "";
        }*/
        $params['target'] = [
            "test_key" => $test_key,
            "fn" => $postParams['firstname'],
            "ln" => $postParams['lastname'],
            "addr" => $addr_clean,
            "city" => $postParams['city'],
            "state" => $postParams['region'],
            "zip" => $postParams['postcode'],
            "age" => '21+',
            "dob" => $dob,
            "ssn" => '854125698',
            "phone" => $postParams['telephone'],
            "email" => $postParams['email']
            ];
        $data_string = json_encode($params);
        try {
            $client = $this->getClient();
            $client->setUri($endpoint);
            $client->setHeaders(
                [
                    'Content-type' => 'application/json',
                    'Accept' => 'application/json'
                ]
            );
            $client->setRawData($data_string, 'application/json');
            //$client->setParameterPost($data_string);
            $apiResponse = $client->request('POST');
            $rawResponse = $apiResponse->getRawBody();
            $response = json_decode($rawResponse,true);
        } catch (\Zend_Http_Client_Exception $e) {
            return false;
        }
        return $response['result']['action'];
    }
}
