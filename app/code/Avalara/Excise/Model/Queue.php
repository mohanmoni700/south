<?php
namespace Avalara\Excise\Model;

class Queue extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Avalara\Excise\Model\ResourceModel\Queue');
    }
}
