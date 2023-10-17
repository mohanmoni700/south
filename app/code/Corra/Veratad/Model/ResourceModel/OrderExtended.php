<?php
namespace Corra\Veratad\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * OrderExtended ResourceModel
 */
class OrderExtended extends AbstractDb
{
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init('sales_order_extended', 'entity_id');
    }
}
