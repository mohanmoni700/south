<?php
namespace HookahShisha\Customization\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class ConfigProvider implements ConfigProviderInterface
{

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */

    protected $scopeConfig;

    /**
     *
     * @param ScopeConfigInterface $scopeConfig
     */

    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Setup on checkout configuratin value
     *
     * @return mixed
     */
    public function getConfig()
    {
        return $additionalVariables['defaultShippingSelectionConf'] = [
            'enable' => $this->getConfigValue('enable'),
            'defaultSelectedShippingName' => $this->getConfigValue('selected_shipping_name'),
            'timeout' => $this->getConfigValue('timeout'),
        ];
    }

    /**
     * Sore configuration values
     *
     * @param string $field
     * @return string
     */
    public function getConfigValue($field)
    {
        $sectionName = "hookahshisha/default_shipping_selection_conf/" . $field;
        return $this->scopeConfig->getValue($sectionName, ScopeInterface::SCOPE_STORE);
    }
}
