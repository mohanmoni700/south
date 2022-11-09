<?php
declare(strict_types=1);

namespace HookahShisha\Customerb2b\Model\Company\Source;

use Magento\Company\Model\Company;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\State;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class HearAboutUs Config
 *
 */
class HearAboutUs implements OptionSourceInterface
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
        $options = [];
        $storeScope = ScopeInterface::SCOPE_STORE;
        $website_code = $this->storeManager->getWebsite()->getCode();
        $config_website = $this->scopeConfig->getValue(self::WEBSITE_CODE, $storeScope);
        $websidecodes = explode(',', $config_website);

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
            'returning_customer' => 'Returning Customer',
            'headquest' => 'Headquest',
            'big' => 'B.I.G.',
            'rtda' => 'RTDA',
            'champs_show' => 'C.H.A.M.P.S. Show',
            'google' => 'Google',
            'yahoo' => 'Yahoo!',
            'other_search_engine' => 'Other Search Engine',
            'flyer' => 'Flyer',
            'friend_family_member' => 'Friend/Family member',
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
            'returning_customer' => 'Returning Customer',
            'shisha_com' => 'Shisha.com',
            'chichaMaps' => 'ChichaMaps',
            'events' => 'Events',
            'google' => 'Google',
            'flyer' => 'Flyer',
            'friend_family_member' => 'Friend/Family member',
            'sales_representative_visit' => 'Sales Representative Visit',
            'instagram' => 'Instagram',
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
