<?php

namespace HookahShisha\CcavenueGraphQl\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\HTTP\Client\Curl;
use HookahShisha\CcavenueGraphQl\Logger\Logger;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * @var Curl
     */
    protected Curl $curl;

    /**
     * @var Logger
     */
    protected Logger $ccavLogger;
  
    /**
     * @var StoreManagerInterface
     */
    protected StoreManagerInterface $storeManager;

    /**
     * @var TimezoneInterface
     */
    protected TimezoneInterface $timezone;
    
    /**
     * Data constructor.
     *
     * @param Context                  $context
     * @param Curl                     $curl
     * @param Logger                   $ccavLogger
     * @param StoreManagerInterface    $storeManager
     * @param TimezoneInterface        $timezone
     */
    public function __construct(
        Context $context,
        Curl $curl,
        Logger $ccavLogger,
        StoreManagerInterface $storeManager,
        TimezoneInterface $timezone
    ) {
        parent::__construct($context);
        $this->curl = $curl;
        $this->ccavLogger = $ccavLogger;
        $this->storeManager = $storeManager;
        $this->timezone = $timezone;
    }//end __construct()

    /**
     * Get config setting data related to ccavenue.
     *
     * @param  string $field
     */
    public function getCcavenueConfigData($field)
    {
        $path    = 'payment/ccavenue/'.$field;
        $storeid = $this->storeManager->getStore()->getStoreId();
        return $this->scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeid);
    }//end getCcavenueConfigData()

    /**
     * Get ccavenue custom config setting data.
     *
     * @param  string $path
     */
    public function getCCaveCusData($path)
    {
        $storeid = $this->storeManager->getStore()->getStoreId();
        return $this->scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeid);
    }//end getCCaveCusData()
   
    /**
     * Initiate CCavenue Status API.
     *
     * @param  string $orderNo
     */
    public function ccavenueStatusApiResponse($orderNo)
    {

        $ccaEncReqData = [];
        $ccaReqData = [];
        $returnEmpData = [];
        
        $apiUrlPath = $this->getCCaveCusData('graphql_ccavenue/general_Setting/api_url');
        $logStatus = (bool)$this->getCCaveCusData('graphql_ccavenue/general_Setting/enable_log');
        if (!empty($apiUrlPath)) {
            $endPoint = $apiUrlPath;
        } else {
            $endPoint = "https://login.ccavenue.ae/apis/servlet/DoWebTrans";
        }
        
        $accessCode = $this->getCcavenueConfigData('access_code');
        $encyptionKey = $this->getCcavenueConfigData('encryption_key');

        $ccaEncReqData['order_no'] = $orderNo;
        $jsonReqData = json_encode($ccaEncReqData);

        $ccaReqData['enc_request'] = $this->encrypt($jsonReqData, $encyptionKey);
        $ccaReqData['access_code'] = $accessCode;
        $ccaReqData['request_type'] = "JSON";
        $ccaReqData['command'] = "orderStatusTracker";
        $ccaReqData['version'] = 1.1;

        $reqData = http_build_query($ccaReqData);

        if (!empty($endPoint) && !empty($accessCode) && !empty($encyptionKey)) {
            $apiUrl = trim($endPoint);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $apiUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_VERBOSE, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $reqData);
            $result = curl_exec($ch);
            curl_close($ch);

            if ($logStatus) {
                $this->ccavLogger->info('api result data:');
                $this->ccavLogger->info(var_export($result, true));
            }

            if ($result !== null && $result !== '' && !empty($result)) {

            //decrypting response
                $status = '';
                $response = '';
                $information = explode('&', $result);

                $dataSize = count($information);

                for ($i = 0; $i < $dataSize; $i++) {
                    $info_value = explode('=', $information[$i]);
                    if ($info_value[0] == 'enc_response') {
                        $response = $this->decrypt(trim($info_value[1]), $encyptionKey);
                    } else {
                        $status = trim($info_value[1]);
                    }
                }

                if ($logStatus) {
                    $this->ccavLogger->info('decrypt response output: '. $response);
                }

                if ($status == 0) {
                    $returnData = json_decode($response, true);
                    return $returnData;
                } else {
                    return $returnEmpData;
                }
            } else {
                return $returnEmpData;
            }
            
        } else {
            return $returnEmpData;
        }
    }//end ccavenueStatusApiResponse()

    /**
     * Encrypt function
     *
     * @param  string $plainText
     * @param  string $key
     */
    public function encrypt($plainText, $key)
    {
        $key           = hex2bin(md5($key));
        $initVector    = pack("C*", 0x00, 0x01, 0x02, 0x03, 0x04, 0x05, 0x06, 0x07, 0x08, 0x09, 0x0a, 0x0b, 0x0c, 0x0d, 0x0e, 0x0f);
        $openMode      = openssl_encrypt($plainText, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $initVector);
        $encryptedText = bin2hex($openMode);
        return $encryptedText;
    }//end encrypt()

    /**
     * Decrypt function
     *
     * @param  string $encryptedText
     * @param  string $key
     */
    public function decrypt($encryptedText, $key)
    {
        $key = hex2bin(md5($key));
        $initVector = pack("C*", 0x00, 0x01, 0x02, 0x03, 0x04, 0x05, 0x06, 0x07, 0x08, 0x09, 0x0a, 0x0b, 0x0c, 0x0d, 0x0e, 0x0f);
        $encryptedText = hex2bin($encryptedText);
        $decryptedText = openssl_decrypt($encryptedText, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $initVector);
        return $decryptedText;
    }//end decrypt()
}//end class
