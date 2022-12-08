<?php
declare(strict_types=1);

namespace HookahShisha\Customerb2b\Model\Company\Source;

use Magento\Company\Model\Company;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\State;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

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
     * @var ScopeConfigInterface $scopeConfig
     */
    protected $scopeConfig;

    /**
     * Store
     *
     * @var StoreManagerInterface $storeManager
     */
    private $storeManager;

    /**
     * @var State $state
     */
    protected $state;

    /**
     * Construct
     *
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param State $state
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        State $state
    ) {
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->state = $state;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $storeScope = ScopeInterface::SCOPE_STORE;
        $website_code = $this->storeManager->getWebsite()->getCode();
        $config_website = $this->scopeConfig->getValue(self::WEBSITE_CODE, $storeScope);
        $websidecodes = explode(',', $config_website);
        $options = [];

        if (in_array($website_code, $websidecodes)) {
            foreach ($this->getOptionArrayHub() as $key => $value) {
                $options[] = ['label' => __($value), 'value' => $key];
            }
        } elseif ($this->state->getAreaCode() == Area::AREA_ADMINHTML) {
            foreach ($this->getOptionArrayAdmin() as $key => $value) {
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
            'international' => 'International',
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

    /**
     * Get merged options array for admin
     *
     * @return array
     */
    public function getOptionArrayAdmin()
    {
        return array_merge($this->getOptionArrayHub(), $this->getOptionArray());
    }
}
