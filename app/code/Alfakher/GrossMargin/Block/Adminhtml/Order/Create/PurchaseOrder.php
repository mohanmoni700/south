<?php

namespace Alfakher\GrossMargin\Block\Adminhtml\Order\Create;

/**
 * @author af_bv_op
 */
class PurchaseOrder extends \Magento\Sales\Block\Adminhtml\Order\Create\AbstractCreate
{
    /**
     * Setting block id
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('sales_order_create_purchase_order');
    }
}
