<?php
declare(strict_types=1);

namespace Alfakher\SlopePayment\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Alfakher\SlopePayment\Model\System\Config\Backend\Environment;

class Config extends AbstractHelper
{
    /**
     * @var EncryptorInterface
     */
    protected $encryptor;
    
    /* General Configurations */

    const XML_PATH_ACTIVE = 'payment/slope_payment/active';
    
    const XML_PATH_TITLE = 'payment/slope_payment/title';

    const XML_PATH_NEW_ORDER_STATUS = 'payment/slope_payment/order_status';

    const XML_PATH_INSTRUCTIONS = 'payment/slope_payment/instructions';

    /* API Credentials Related Settings */

    const XML_PATH_ENVIRONMENT = 'payment/slope_payment/environment';

    const XML_PATH_PUBLIC_KEY_PRODUCTION = 'payment/slope_payment/publickey_production';

    const XML_PATH_PRIVATE_KEY_PRODUCTION = 'payment/slope_payment/privatekey_production';

    const XML_PATH_API_ENDPOINT_URL_PRODUCTION = 'payment/slope_payment/endpoint_production';

    const XML_PATH_JS_URL_PRODUCTION = 'payment/slope_payment/slopejs_production';

    const XML_PATH_PUBLIC_KEY_SANDBOX = 'payment/slope_payment/publickey_sandbox';
    
    const XML_PATH_PRIVATE_KEY_SANDBOX = 'payment/slope_payment/privatekey_sandbox';

    const XML_PATH_API_ENDPOINT_URL_SANDBOX = 'payment/slope_payment/endpoint_sandbox';
    
    const XML_PATH_JS_URL_SANDBOX = 'payment/slope_payment/slopejs_sandbox';

    /* Advanced Slope Settings */

    const XML_PATH_DEBUG_ENABLED = 'payment/slope_payment/debug';
   

    /**
     * @param Context $context
     * @param EncryptorInterface $encryptor
     */
    public function __construct(
        Context $context,
        EncryptorInterface $encryptor
    ) {
        parent::__construct($context);
        $this->encryptor = $encryptor;
    }

    /**
     * Is Slope Payment active
     *
     * @return bool
     */
    public function isSlopePaymentActive()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ACTIVE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Retrieve slope payment method title
     *
     * @return string
     */
    public function getSlopeTitle()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_TITLE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Retrieve new order status
     *
     * @return string
     */
    public function getNewOrderStatus()
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_NEW_ORDER_STATUS,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get slope instructions
     *
     * @return string
     */
    public function getSlopeInstructions()
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_INSTRUCTIONS,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get slope environment type
     *
     * @return string
     */
    public function getEnvironmentType()
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_ENVIRONMENT,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get slope production public key
     *
     * @return string
     */
    public function getProductionPublicKey()
    {
        $prodPublicKey = $this->scopeConfig->getValue(
            self::XML_PATH_PUBLIC_KEY_PRODUCTION,
            ScopeInterface::SCOPE_STORE
        );
        $prodPublicKey = $this->encryptor->decrypt($prodPublicKey);
        return $prodPublicKey;
    }

    /**
     * Get slope production private key
     *
     * @return string
     */
    public function getProductionPrivateKey()
    {
        $prodPrivateKey = $this->scopeConfig->getValue(
            self::XML_PATH_PRIVATE_KEY_PRODUCTION,
            ScopeInterface::SCOPE_STORE
        );
        $prodPrivateKey = $this->encryptor->decrypt($prodPrivateKey);
        return $prodPrivateKey;
    }

    /**
     * Get slope production api endpoint url
     *
     * @return string
     */
    public function getProductionApiEndpointUrl()
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_API_ENDPOINT_URL_PRODUCTION,
            ScopeInterface::SCOPE_STORE
        );
    }
    
    /**
     * Get slope production js url
     *
     * @return string
     */
    public function getProductionJsUrl()
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_JS_URL_PRODUCTION,
            ScopeInterface::SCOPE_STORE
        );
    }


    /**
     * Get slope sandbox public key
     *
     * @return string
     */
    public function getSandboxPublicKey()
    {
        $sandPublicKey = $this->scopeConfig->getValue(
            self::XML_PATH_PUBLIC_KEY_SANDBOX,
            ScopeInterface::SCOPE_STORE
        );
        $sandPublicKey = $this->encryptor->decrypt($sandPublicKey);
        return $sandPublicKey;
    }

    /**
     * Get slope sandbox private key
     *
     * @return string
     */
    public function getSandboxPrivateKey()
    {
        $sandPrivateKey = $this->scopeConfig->getValue(
            self::XML_PATH_PRIVATE_KEY_SANDBOX,
            ScopeInterface::SCOPE_STORE
        );
        $sandPrivateKey = $this->encryptor->decrypt($sandPrivateKey);
        return $sandPrivateKey;
    }

    /**
     * Get slope sandbox api endpoint url
     *
     * @return string
     */
    public function getSandboxApiEndpointUrl()
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_API_ENDPOINT_URL_SANDBOX,
            ScopeInterface::SCOPE_STORE
        );
    }
    
    /**
     * Get slope sandbox js url
     *
     * @return string
     */
    public function getSandboxJsUrl()
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_JS_URL_SANDBOX,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Is slope debug active
     *
     * @return bool
     */
    public function isSlopeDebugActive()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_DEBUG_ENABLED,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getEndpointUrl()
    {
        $environment = $this->getEnvironmentType();

        if ($environment == Environment::ENVIRONMENT_SANDBOX) {
            $apiEndpointUrl = $this->getSandboxApiEndpointUrl();
        } else {
            $apiEndpointUrl = $this->getProductionApiEndpointUrl();
        }

        return $apiEndpointUrl;
    }

    public function getJsSrcForCheckoutPage()
    {
        $environment = $this->getEnvironmentType();

        if ($environment == Environment::ENVIRONMENT_SANDBOX) {
            $jsUrl = $this->getSandboxJsUrl();
            $publicKey = $this->getSandboxPublicKey();
        } else {
            $jsUrl = $this->getProductionJsUrl();
            $publicKey = $this->getProductionPublicKey();
        }
        
        $jsUrl = $jsUrl.'?pk='.$publicKey;
        return $jsUrl;
    }
}
