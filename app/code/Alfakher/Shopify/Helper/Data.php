<?php
namespace Alfakher\Shopify\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Key
     *
     * @var EncrytionKey
     */
    private $encryption_key;
    /**
     * Key
     *
     * @var SignatureKey
     */
    private $signature_key;
    /**
     * @var \Magento\Framework\HTTP\Client\Curl
     */
    protected $curl;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    public const SHOPIFY_ENABLE = 'shopify/general/enable';
    public const SHOPIFY_TOKEN = 'shopify/general/shopify_token';
    public const SHOPIFY_DOMAIN = 'shopify/general/shopify_domain';

    /**
     * Constructor
     * @param \Alfakher\Shopify\Logger\Logger $logger
     * @param \Magento\Framework\HTTP\Client\Curl $curl
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Alfakher\Shopify\Logger\Logger $logger,
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->logger = $logger;
        $this->curl = $curl;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Generate Key
     *
     * @param num $multipass_secret
     */
    public function generateKey($multipass_secret)
    {
        // Use the Multipass secret to derive two cryptographic keys,
        // one for encryption, one for signing
        $key_material = hash("sha256", $multipass_secret, true);
        $this->encryption_key = substr($key_material, 0, 16);
        $this->signature_key = substr($key_material, 16, 16);
    }

    /**
     * Generate Token
     *
     * @param string $customer_data_hash
     */
    public function generateToken($customer_data_hash)
    {
        // Store the current time in ISO8601 format.
        // The token will only be valid for a small timeframe around this timestamp.
        $customer_data_hash["created_at"] = date("c");

        // Serialize the customer data to JSON and encrypt it
        $ciphertext = $this->encrypt(json_encode($customer_data_hash));

        // Create a signature (message authentication code) of the ciphertext
        // and encode everything using URL-safe Base64 (RFC 4648)
        return strtr(base64_encode($ciphertext . $this->sign($ciphertext)), '+/', '-_');
    }

    /**
     * Encrypt plaintext
     *
     * @param string $plaintext
     */
    private function encrypt($plaintext)
    {
        // Use a random IV
        $iv = openssl_random_pseudo_bytes(16);

        // Use IV as first block of ciphertext
        return $iv . openssl_encrypt($plaintext, "AES-128-CBC", $this->encryption_key, OPENSSL_RAW_DATA, $iv);
    }
    /**
     * Sign
     *
     * @param string $data
     */
    private function sign($data)
    {
        return hash_hmac("sha256", $data, $this->signature_key, true);
    }
    /**
     * Get Customer data
     *
     * @param data $customer_data
     */
    public function customerData($customer_data)
    {
        $shopify_enable = $this->scopeConfig->getValue(self::SHOPIFY_ENABLE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        if ($shopify_enable == 1) {
        // if there is no email, just go to the shop
            if (!isset($customer_data['email']) || $customer_data['email'] == "") {
                $this->logger->info(print_r($customer_data, true));
            } else {
                
                $generateKey = $this->scopeConfig->getValue(self::SHOPIFY_TOKEN, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                $this->generateKey($generateKey);

                $token = $this->generateToken($customer_data);
                
                $shopify_domain = $this->scopeConfig->getValue(self::SHOPIFY_DOMAIN, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                $url = "https://" . $shopify_domain . ".myshopify.com/account/login/multipass/" . $token;

                $curl = [
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                ];
                $this->curl->setOptions($curl);
                $this->curl->get($url);
                $response = $this->curl->getBody();
                $this->logger->info(print_r($response, true));
            }
        }
    }
}
