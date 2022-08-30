<?php

namespace Alfakher\Seamlesschex\Plugin;

class UpdateCheck
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
     * Around execute
     *
     * @param \Alfakher\GrossMargin\Observer\OrderEditTaxCalculation $subject
     * @param callable $proceed
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function aroundExecute(
        \Alfakher\GrossMargin\Observer\OrderEditTaxCalculation $subject,
        callable $proceed,
        \Magento\Framework\Event\Observer $observer
    ) {
        $order = $observer->getEvent()->getOrder();
        $result = $proceed($observer);
        $this->_seamlesschexHelper->updateCheck($order);

        return $result;
    }
}
