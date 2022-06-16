<?php

namespace HookahShisha\Customization\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class RemoveYotpoTab implements ObserverInterface
{
    /**
     * Get config value
     *
     * @var ScopeConfig
     */
    protected $_scopeConfig;

    /**
     *
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->_scopeConfig = $scopeConfig;
    }

    /**
     * Execute block action
     *
     * @param string $observer
     * @return Unset Block
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Framework\View\Element\Template $block */
        $layout = $observer->getLayout();
        $block = $layout->getBlock('yotpo_tab');
        $remove = $this->_scopeConfig->getValue(
            'yotpo_loyalty/advanced/myaccount_yotpo',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        if (empty($remove)) {
            $layout->unsetElement('yotpo_tab');
        }
    }
}
