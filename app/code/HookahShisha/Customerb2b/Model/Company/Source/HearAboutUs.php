<?php

namespace HookahShisha\Customerb2b\Model\Company\Source;

use Magento\Company\Model\Company;
use Magento\Framework\Data\OptionSourceInterface;

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

        $options = [];
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $website_code = $this->storeManager->getWebsite()->getCode();
        $config_website = $this->scopeConfig->getValue(self::WEBSITE_CODE, $storeScope);

        if ($website_code === $config_website) {
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
}
