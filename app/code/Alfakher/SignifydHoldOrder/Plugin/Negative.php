<?php
namespace Alfakher\SignifydHoldOrder\Plugin;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Option\ArrayInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Option data for negative order actions
 */
class Negative
{
    /**
     * @var ScopeConfigInterface
     */
    protected $coreConfig;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * Negative constructor.
     * @param ScopeConfigInterface $coreConfig
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        ScopeConfigInterface $coreConfig,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->coreConfig = $coreConfig;
        $this->request = $request;
    }

    public function aroundToOptionArray()
    {
        $options = [
            [
                'value' => 'nothing',
                'label' => 'Do nothing'
            ],
            [
                'value' => 'cancel',
                'label' => 'Cancel order'
            ],
            [
                'value' => 'refund',
                'label' => 'Create credit memo'
            ],
            [
                'value' => 'hold',
                'label' => 'Leave on hold',
            ]
        ];

        $store = $this->request->getParam('store');
        $website = $this->request->getParam('website');
        $negativeConfigPath = 'signifyd/advanced/guarantee_negative_action';

        if (empty($store)) {
            if (empty($website)) {
                $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT;
                $scopeCode = null;
            } else {
                $scopeType = ScopeInterface::SCOPE_WEBSITE;
                $scopeCode = $website;
            }
        } else {
            $scopeType = ScopeInterface::SCOPE_STORE;
            $scopeCode = $store;
        }
        
        return $options;
    }
}
