<?php
namespace Avalara\Excise\Model\ResourceModel;

class Queue extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('excise_queue', 'queue_id');
    }
}
