<?php

namespace Alfakher\HandlingFee\ViewModel;

/**
 *
 */
class HandlingFee implements \Magento\Framework\View\Element\Block\ArgumentInterface
{

    const MODULE_ENABLE = "hookahshisha/handling_fee_group/handling_fee_enable";
    const HANDLING_FEE_TYPE = "hookahshisha/handling_fee_group/handling_fee_type";
    const HANDLING_FEE = "hookahshisha/handling_fee_group/handling_fee";

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    public function isModuleEnabled($websiteId)
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE;
        return $this->scopeConfig->getValue(self::MODULE_ENABLE, $storeScope, $websiteId);
    }
}
