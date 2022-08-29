<?php

namespace Alfakher\Seamlesschex\Plugin;

/**
 * 
 */
class UpdateCheck
{

	public function __construct(
		\Alfakher\Seamlesschex\Helper\Data $seamlesschexHelper
	) {
		$this->_seamlesschexHelper = $seamlesschexHelper;
	}
	
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