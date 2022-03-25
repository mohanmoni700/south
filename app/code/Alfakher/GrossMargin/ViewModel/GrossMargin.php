<?php

namespace Alfakher\GrossMargin\ViewModel;

/**
 * View model class
 *
 * @author af_bv_op
 */
class GrossMargin implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    const MODULE_ENABLE = "hookahshisha/gross_margin_group/gross_margin_enable";

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
