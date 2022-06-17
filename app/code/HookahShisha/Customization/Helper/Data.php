<?php
namespace HookahShisha\Customization\Helper;

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

        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/check.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        // if there is no email, just go to the shop
        if (!isset($customer_data['email']) || $customer_data['email'] == "") {
            $logger->info(print_r($customer_data, true));
        } else {
            $multipass = $this->generateKey("2115eee9605b73c8905d059074dd55e4");
            $token = $this->generateToken($customer_data);
            $shopify_domain = "hookahshisha-express";
            $url = "https://" . $shopify_domain . ".myshopify.com/account/login/multipass/" . $token;
            //header('Location: '.$url);

            $curl = curl_init();

            curl_setopt_array($curl, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
            ]);

            $response = curl_exec($curl);
            curl_close($curl);
            $logger->info(print_r($response, true));
        }
    }
}
