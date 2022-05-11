<?php

namespace Alfakher\OfflinePaymentRecords\Model;

/**
 *
 */
class OfflinePaymentRecord extends \Magento\Framework\Model\AbstractModel {

	protected function _construct() {
		$this->_init('Alfakher\OfflinePaymentRecords\Model\ResourceModel\OfflinePaymentRecord');
	}
}