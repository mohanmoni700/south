<?php

namespace Alfakher\OfflinePaymentRecords\Model\ResourceModel;

class OfflinePaymentRecord extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    /**
     * Construct
     */
    protected function _construct()
    {
        $this->_init('alfakher_offline_payment_records', 'entity_id');
    }
}
