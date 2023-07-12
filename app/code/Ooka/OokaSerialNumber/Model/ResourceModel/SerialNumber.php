<?php
declare (strict_types = 1);

namespace Ooka\OokaSerialNumber\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class SerialNumber extends AbstractDb
{
    /**
     * Initialize connection
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('product_serial_code', 'id');
    }
}
