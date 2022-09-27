<?php

namespace Alfakher\Seamlesschex\Observer;

class CancelCheck implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * Construct
     *
     * @param \Alfakher\Seamlesschex\Helper\Data $seamlesschexHelper
     */
    public function __construct(
        \Alfakher\Seamlesschex\Helper\Data $seamlesschexHelper
    ) {
        $this->_seamlesschexHelper = $seamlesschexHelper;
    }
    
    /**
     * Execute method
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(
        \Magento\Framework\Event\Observer $observer
    ) {
        $order = $observer->getEvent()->getData('order');
        $this->_seamlesschexHelper->voidCheck($order);
    }
}
