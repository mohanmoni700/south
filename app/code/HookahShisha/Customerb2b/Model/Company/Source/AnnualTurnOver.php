<?php
declare(strict_types=1);

namespace HookahShisha\Customerb2b\Model\Company\Source;

use Magento\Company\Model\Company;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class AnnualTurnOver Config
 */
class AnnualTurnOver implements \Magento\Framework\Data\OptionSourceInterface
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
     * Construct
     *
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig
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
        $options = [];
        $storeScope = ScopeInterface::SCOPE_STORE;
        $website_code = $this->storeManager->getWebsite()->getCode();
        $config_website = $this->scopeConfig->getValue(self::WEBSITE_CODE, $storeScope);
        $websidecodes = explode(',', $config_website);

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
            1 => '0-20000',
            2 => '20001-50000',
            3 => '>50001',
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
            4 => '0-50000',
            5 => '50000-200000',
            6 => '200001-500000',
            7 => '>500000',
        ];
    }
}
