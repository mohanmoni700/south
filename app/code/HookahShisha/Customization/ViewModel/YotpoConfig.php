<?php
namespace HookahShisha\Customization\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;

class YotpoConfig implements ArgumentInterface
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Sore configuration values
     *
     * @param string $section
     * @return string
     */
    public function getConfigValue($section)
    {
        return $this->scopeConfig->getValue($section, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * Gross margin configuration value
     *
     * @param string $section
     * @param string $websiteid
     * @return string
     */
    public function isModuleEnabled($section, $websiteid)
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE;
        return $this->scopeConfig->getValue($section, $storeScope, $websiteid);
    }
}
