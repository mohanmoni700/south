<?php

namespace Alfakher\HandlingFee\Helper;

/**
 *
 */
use Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper
{
    const MODULE_ENABLE = "hookahshisha/handling_fee_group/handling_fee_enable";
    const HANDLING_FEE_TYPE = "hookahshisha/handling_fee_group/handling_fee_type";
    const HANDLING_FEE = "hookahshisha/handling_fee_group/handling_fee";

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->_storeManager = $storeManager;
        parent::__construct($context);
    }

    public function isModuleEnabled()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE;
        $websiteId = $this->_storeManager->getStore()->getWebsiteId();
        return $this->scopeConfig->getValue(self::MODULE_ENABLE, $storeScope, $websiteId);
    }

    public function getHandlingFee($subtotal)
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE;
        $websiteId = $this->_storeManager->getStore()->getWebsiteId();
        $type = $this->scopeConfig->getValue(self::HANDLING_FEE_TYPE, $storeScope, $websiteId);

        if ($type == 'fixed') {
            return $this->scopeConfig->getValue(self::HANDLING_FEE, $storeScope, $websiteId);
        } else {
            $feePercentage = $this->scopeConfig->getValue(self::HANDLING_FEE, $storeScope, $websiteId);
            return round(($subtotal * ($feePercentage / 100)), 2);
        }
    }

}
