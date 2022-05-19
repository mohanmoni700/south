<?php

namespace Alfakher\OfflinePaymentRecords\Model\ResourceModel\OfflinePaymentRecord;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * Construct
     */
    protected function _construct()
    {
        $this->_init(
            \Alfakher\OfflinePaymentRecords\Model\OfflinePaymentRecord::class,
            \Alfakher\OfflinePaymentRecords\Model\ResourceModel\OfflinePaymentRecord::class
        );
    }
}
