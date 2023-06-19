<?php

namespace Alfakher\HandlingFee\ViewModel;

use HookahShisha\Customization\Plugin\Magetrend\Order\Pdf\MagetrendInvoice;

class Fee implements \Magento\Framework\View\Element\Block\ArgumentInterface
{

    public const MODULE_ENABLE = "hookahshisha/handling_fee_group/handling_fee_enable";
    public const HANDLING_FEE_TYPE = "hookahshisha/handling_fee_group/handling_fee_type";
    public const HANDLING_FEE = "hookahshisha/handling_fee_group/handling_fee";
    public const SUBTOTAL_FEE = "hookahshisha/af_discount_group/subtotal_enable";
    public const SHIPPING_FEE = "hookahshisha/af_discount_group/shipping_enable";
    public const ZERO_OUT = "hookahshisha/af_discount_group/zero_out_enable";
    public const IS_SUBTOTAL_INCL_TAX = "tax/sales_display/subtotal";
    public const IS_SHIPPING_INCL_TAX = "tax/sales_display/shipping";

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

    /**
     * Excise note message
     *
     * @param string $section
     * @return string
     */
    public function getExciseNote($section)
    {
        return $this->scopeConfig->getValue($section, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * can Show tax column for this invoice
     * @param $order
     * @return mixed
     */
    public function canShowTaxColumn($order)
    {
        $address = $order->getShippingAddress();
        if ($address->getRegionId() == MagetrendInvoice::KN_REGION_ID) {
            return true;
        }
        return false;
    }
}
