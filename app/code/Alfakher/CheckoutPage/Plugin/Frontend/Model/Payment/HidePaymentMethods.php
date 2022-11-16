<?php
declare(strict_types=1);

namespace Alfakher\CheckoutPage\Plugin\Frontend\Model\Payment;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Payment\Model\MethodList;

class HidePaymentMethods
{
    public const PAYMENT = 'hookahshisha/restricted_payment_methods/hide_payments';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var Json
     */
    protected $json;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Constructor for class HidePaymentMethods
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param Json $json
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Json $json,
        StoreManagerInterface $storeManager
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->json = $json;
        $this->storeManager = $storeManager;
    }

    /**
     * Remove specific payment method from the list of available payment methods
     *
     * @param MethodList $subject
     * @param array $availableMethods
     * @param CartInterface|null $quote
     * @return array
     */
    public function afterGetAvailableMethods(
        MethodList $subject,
        $availableMethods,
        CartInterface $quote = null
    ) {
        $countryCode = $this->getCountryCodeFromQuote($quote);
        $paymentConfigs = $this->getPaymentMethodConfig();
        if ($paymentConfigs && $paymentConfigs != '' && $paymentConfigs != null) {
            foreach ($paymentConfigs as $key => $configMethod) {
                foreach ($availableMethods as $key => $method) {
                    $countryIds = explode(',', $configMethod['country_codes']);
                    if (($method->getCode() == $configMethod['payment_method']) &&
                    (!in_array($countryCode, $countryIds))) {
                        unset($availableMethods[$key]);
                    }
                }
            }

        }
        return $availableMethods;
    }

    /**
     * Get country code from the quote
     *
     * @param CartInterface $quote
     * @return string
     */
    private function getCountryCodeFromQuote($quote)
    {
        if ($quote) {
            return $quote->getShippingAddress()->getCountryId();
        }
        return '';
    }

    /**
     * Get payment method configuration
     *
     * @return array
     */
    public function getPaymentMethodConfig()
    {
        $paymentconfigArray = [];
        $paymentconfig = $this->getConfigValue(self::PAYMENT);
        if ($paymentconfig) {
            $unserializeData = $this->json->unserialize($paymentconfig, true);
            foreach ($unserializeData as $key => $row) {
                $paymentconfigArray[] = $row;
            }
            return $paymentconfigArray;
        }
        return $paymentconfigArray;
    }

    /**
     * Get Config Value from given config path
     *
     * @param string $configPath
     * @return string
     */
    public function getConfigValue($configPath)
    {
        return $this->scopeConfig->getValue(
            $configPath,
            ScopeInterface::SCOPE_STORE,
            $this->storeManager->getStore()->getStoreId()
        );
    }
}
