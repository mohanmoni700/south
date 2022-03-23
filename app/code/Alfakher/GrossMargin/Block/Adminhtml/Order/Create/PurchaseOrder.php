<?php

namespace Alfakher\GrossMargin\Block\Adminhtml\Order\Create;

/**
 *
 */
class PurchaseOrder extends \Magento\Sales\Block\Adminhtml\Order\Create\AbstractCreate
{

    protected function _construct()
    {
        parent::_construct();
        $this->setId('sales_order_create_purchase_order');
    }
}
