<?php

namespace HookahShisha\Customerb2b\Model\Company\Source;

use Magento\Company\Model\Company;

/**
 * Class Businesstype Config
 */
class Businesstype implements \Magento\Framework\Data\OptionSourceInterface
{

    /**
     * Website Code
     */
    public const WEBSITE_CODE = 'hookahshisha/website_code_setting/website_code';

    /**
     * Scope Configuration
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Store
     *
     * @var \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    private $storeManager;

    /**
     * Construct
     *
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {

        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $website_code = $this->storeManager->getWebsite()->getCode();
        $config_website = $this->scopeConfig->getValue(self::WEBSITE_CODE, $storeScope);
        $websidecodes = explode(',', $config_website);
        $options = [];

        if (in_array($website_code, $websidecodes)) {
            foreach ($this->getOptionArrayHub() as $key => $value) {
                $options[] = ['label' => __($value), 'value' => $key];
            }
        } else {
            foreach ($this->getOptionArray() as $key => $value) {
                $options[] = ['label' => __($value), 'value' => $key];
            }
        }
        return $options;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function getOptionArray()
    {
        return [
            'distributor' => 'Distributor',
            'retail' => 'Retail',
            'hookah_lounge' => 'Hookah Lounge',
            'catering' => 'Catering',
            'bar_night_club' => 'Bar/Night Club',
        ];
    }

    /**
     * Get options
     *
     * @return array
     */
    public function getOptionArrayHub()
    {
        return [
            'distributor' => 'Distributor',
            'wholesaler' => 'Wholesaler',
            'retail_shop' => 'Retail Shop',
            'hookah_lounge' => 'Hookah Lounge',
            'catering' => 'Catering',
            'bar_night_club' => 'Bar/Night Club',
            'specialty_shop' => 'Specialty Shop',
            'tobacconist' => 'Tobacconist',
        ];
    }
}
