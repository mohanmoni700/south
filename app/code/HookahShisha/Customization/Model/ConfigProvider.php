<?php
namespace HookahShisha\Customization\Model;

use Magento\Checkout\Model\ConfigProviderInterface;

class ConfigProvider implements ConfigProviderInterface
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
     * setup on checkout configuratin value
     *
     * @return mixed
     */
    public function getConfig()
    {
        $additionalVariables['defaultShippingSelectionConf'] = [
            'enable' => $this->getConfigValue('hookahshisha/default_shipping_selection_conf/enable'),
            'defaultSelectedShippingName' => $this->getConfigValue('hookahshisha/default_shipping_selection_conf/selected_shipping_name'),
            'timeout' => $this->getConfigValue('hookahshisha/default_shipping_selection_conf/timeout'),
        ];
        return $additionalVariables;
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
}
