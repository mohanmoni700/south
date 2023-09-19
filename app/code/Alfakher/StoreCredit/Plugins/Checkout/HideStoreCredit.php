<?php

declare(strict_types=1);

namespace Alfakher\StoreCredit\Plugins\Checkout;

use Magento\Checkout\Block\Checkout\LayoutProcessor;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 *  class for hide store credit from checkout
 */
class HideStoreCredit
{
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Check the scopeconfig if its false hide the Storecredit from Checkout
     *
     * @param LayoutProcessor $subject
     * @param array $result
     * @return array
     */
    public function afterProcess(LayoutProcessor $subject, $result)
    {
        // Check if the configuration is set and has a specific value
        $isStoreCreditEnabled = $this->scopeConfig->getValue(
            'customer/magento_customerbalance/is_disable',
                        ScopeInterface::SCOPE_STORE
        );

        if (!$isStoreCreditEnabled) {

            // If the configuration is set to false, remove the storeCredit component
            unset(
                $result['components']['checkout']['children']['steps']['children']
                ['billing-step']['children']['payment']['children']
                ['afterMethods']['children']['storeCredit']
            );
        }

        return $result;
    }
}
