<?php

namespace Alfakher\OfflinePaymentRecords\Model\ResourceModel\OfflinePaymentRecord;

/**
 *
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection {

	protected function _construct() {
		$this->_init(
			'Alfakher\OfflinePaymentRecords\Model\OfflinePaymentRecord',
			'Alfakher\OfflinePaymentRecords\Model\ResourceModel\OfflinePaymentRecord'
		);
	}
}