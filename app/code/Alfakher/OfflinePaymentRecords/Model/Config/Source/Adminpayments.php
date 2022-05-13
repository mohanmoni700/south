<?php

namespace Alfakher\OfflinePaymentRecords\Model\Config\Source;

/**
 *
 */
use \Magento\Payment\Model\Config;

class Adminpayments extends \Magento\Framework\DataObject implements \Magento\Framework\Data\OptionSourceInterface {

	protected $_appConfigScopeConfigInterface;
	protected $_paymentModelConfig;

	public function __construct(
		\Magento\Payment\Model\Config\Source\Allmethods $allPaymentMethod
	) {
		$this->allPaymentMethod = $allPaymentMethod;
	}

	public function toOptionArray() {
		return $this->allPaymentMethod->toOptionArray();
	}
}