<?php

namespace Alfakher\HandlingFee\ViewModel;

/**
 * View model class
 *
 * @author af_bv_op
 */
class HandlingFee implements \Magento\Framework\View\Element\Block\ArgumentInterface
{

    const MODULE_ENABLE = "hookahshisha/handling_fee_group/handling_fee_enable";
    const HANDLING_FEE_TYPE = "hookahshisha/handling_fee_group/handling_fee_type";
    const HANDLING_FEE = "hookahshisha/handling_fee_group/handling_fee";

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
}
