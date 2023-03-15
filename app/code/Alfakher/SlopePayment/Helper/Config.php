<?php
declare(strict_types=1);

namespace Alfakher\SlopePayment\Helper;

use Alfakher\SlopePayment\Model\System\Config\Backend\Environment;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Company\Api\CompanyManagementInterface;
use Magento\Company\Api\Data\CompanyInterface;

class Config extends AbstractHelper
{
    /**
     * @var EncryptorInterface
     */
    protected $encryptor;

    /**
     * @var CompanyInterface
     */
    protected $company = null;

    /**
     * @var CompanyManagementInterface
     */
    protected $companyManagement;

    /* General Configurations */

    public const XML_PATH_ACTIVE = 'payment/slope_payment/active';

    public const XML_PATH_TITLE = 'payment/slope_payment/title';

    public const XML_PATH_NEW_ORDER_STATUS = 'payment/slope_payment/order_status';

    public const XML_PATH_INSTRUCTIONS = 'payment/slope_payment/instructions';

    /* API Credentials Related Settings */

    public const XML_PATH_ENVIRONMENT = 'payment/slope_payment/environment';

    public const XML_PATH_PUBLIC_KEY_PRODUCTION = 'payment/slope_payment/publickey_production';

    public const XML_PATH_PRIVATE_KEY_PRODUCTION = 'payment/slope_payment/privatekey_production';

    public const XML_PATH_API_ENDPOINT_URL_PRODUCTION = 'payment/slope_payment/endpoint_production';

    public const XML_PATH_JS_URL_PRODUCTION = 'payment/slope_payment/slopejs_production';

    public const XML_PATH_PUBLIC_KEY_SANDBOX = 'payment/slope_payment/publickey_sandbox';

    public const XML_PATH_PRIVATE_KEY_SANDBOX = 'payment/slope_payment/privatekey_sandbox';

    public const XML_PATH_API_ENDPOINT_URL_SANDBOX = 'payment/slope_payment/endpoint_sandbox';

    public const XML_PATH_JS_URL_SANDBOX = 'payment/slope_payment/slopejs_sandbox';

    /* Advanced Slope Settings */

    public const XML_PATH_DEBUG_ENABLED = 'payment/slope_payment/debug';

    /**
     * @param Context $context
     * @param EncryptorInterface $encryptor
     * @param CompanyManagementInterface $companyManagement
     */
    public function __construct(
        Context $context,
        EncryptorInterface $encryptor,
        CompanyManagementInterface $companyManagement
    ) {
        parent::__construct($context);
        $this->encryptor = $encryptor;
        $this->companyManagement = $companyManagement;
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
        return (string) $this->scopeConfig->getValue(
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
        return (string) $this->scopeConfig->getValue(
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
        return (string) $this->scopeConfig->getValue(
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
        return (string) $this->scopeConfig->getValue(
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
        return (string) $this->scopeConfig->getValue(
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
        return (string) $this->scopeConfig->getValue(
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
        return (string) $this->scopeConfig->getValue(
            self::XML_PATH_JS_URL_SANDBOX,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Is slope debug active
     *
     * @return bool
     */
    public function isDebugEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_DEBUG_ENABLED,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get Slope API Endpoint URL
     *
     * @return string
     */
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

    /**
     * Get Js Src for slope checkout widget
     *
     * @return string
     */
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
        $jsUrl = $jsUrl . '?pk=' . $publicKey;
        return $jsUrl;
    }

    /**
     * Get slope formatted phone number
     *
     * @param string $phone
     * @return string
     */
    public function getSlopeFormattedPhone($phone = '')
    {
        return '+'.preg_replace('/\W/', '', $phone);
    }

    /**
     * Get current customer's company
     *
     * @param int $customerId
     * @return CompanyInterface
     */
    public function getCustomerCompany($customerId)
    {
        if ($this->company !== null) {
            return $this->company;
        }
        if ($customerId) {
            $this->company = $this->companyManagement->getByCustomerId($customerId);
        }
        return $this->company;
    }
}
