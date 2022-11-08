<?php
namespace HookahShisha\Customization\ViewModel;

use Magento\Bundle\Block\Sales\Order\Items\Renderer;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class YotpoConfig implements ArgumentInterface
{
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Constructor
     *
     * @param Renderer $renderer
     * @param ScopeConfigInterface $scopeConfig
     */

    public function __construct(
        Renderer $renderer,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->renderer = $renderer;
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

    /**
     * Remove price for bundle options
     *
     * @param mixed $item
     * @return string
     */
    public function getCustomBundlePrice($item)
    {
        if ($attributes = $this->renderer->getSelectionAttributes($item)) {
            return sprintf('%d', $attributes['qty']) . ' x ' . $this->renderer->escapeHtml($item->getName());
        }
        return $this->renderer->escapeHtml($item->getName());
    }
}
