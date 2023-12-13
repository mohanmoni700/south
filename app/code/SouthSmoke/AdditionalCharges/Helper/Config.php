<?php
declare (strict_types = 1);

namespace SouthSmoke\AdditionalCharges\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Config extends AbstractHelper
{
    private const CONFIG_COMPLIANCE_INSURANCE_PATH = 'insurance/compliance_insurance/';
    private const CONFIG_SHIPPING_INSURANCE_PATH = 'insurance/shipping_insurance/';

    public const STATUS = 'status';
    public const AMOUNT = 'amount';
    public const MINIMUM_ORDER_AMOUNT = 'minimum_order_amount';
    public const PERCENT = 'percent';

    /**
     * Get Compliance Insurance Config Paths
     *
     * @param string $path
     * @param string|null $store
     * @return mixed
     */
    public function getComplianceInsurance(string $path, string $store = null)
    {

        $storeScope = ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue(self::CONFIG_COMPLIANCE_INSURANCE_PATH.$path, $storeScope, $store);
    }

    /**
     * Get Shipping Insurance Config Paths
     *
     * @param string $path
     * @param string|null $store
     * @return mixed
     */
    public function getShippingInsurance(string $path, string $store = null)
    {

        $storeScope = ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue(self::CONFIG_SHIPPING_INSURANCE_PATH.$path, $storeScope, $store);
    }
}
