<?php

namespace Alfakher\HandlingFee\ViewModel;

class Fee implements \Magento\Framework\View\Element\Block\ArgumentInterface
{

    const MODULE_ENABLE = "hookahshisha/handling_fee_group/handling_fee_enable";
    const HANDLING_FEE_TYPE = "hookahshisha/handling_fee_group/handling_fee_type";
    const HANDLING_FEE = "hookahshisha/handling_fee_group/handling_fee";
    const SUBTOTAL_FEE = "hookahshisha/af_discount_group/subtotal_enable";
    const SHIPPING_FEE = "hookahshisha/af_discount_group/shipping_enable";
    const ZERO_OUT = "hookahshisha/af_discount_group/zero_out_enable";
    const IS_SUBTOTAL_INCL_TAX = "tax/sales_display/subtotal";
    const IS_SHIPPING_INCL_TAX = "tax/sales_display/shipping";

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Check if module is enable
     *
     * @param int $websiteId
     */
    public function isModuleEnabled($websiteId)
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE;
        return $this->scopeConfig->getValue(self::MODULE_ENABLE, $storeScope, $websiteId);
    }

    /**
     * Check if subtotal edit is enable
     *
     * @param int $websiteId
     */
    public function isSubtotalEditEnabled($websiteId)
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE;
        return $this->scopeConfig->getValue(self::SUBTOTAL_FEE, $storeScope, $websiteId);
    }

    /**
     * Check if shipping fee edit is enable
     *
     * @param int $websiteId
     */
    public function isShippingFeeEditEnabled($websiteId)
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE;
        return $this->scopeConfig->getValue(self::SHIPPING_FEE, $storeScope, $websiteId);
    }

    /**
     * Check if zero out order is enable
     *
     * @param int $websiteId
     */
    public function isZeroOutEnabled($websiteId)
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE;
        return $this->scopeConfig->getValue(self::ZERO_OUT, $storeScope, $websiteId);
    }

    /**
     * Check if need to display subtotal including tax
     *
     * @param int $websiteId
     */
    public function isSubtotalInclTax($websiteId)
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE;
        return $this->scopeConfig->getValue(self::IS_SUBTOTAL_INCL_TAX, $storeScope, $websiteId);
    }

    /**
     * Check if need to display shipping including tax
     *
     * @param int $websiteId
     */
    public function isShippingInclTax($websiteId)
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE;
        return $this->scopeConfig->getValue(self::IS_SHIPPING_INCL_TAX, $storeScope, $websiteId);
    }
}
