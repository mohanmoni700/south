<?php
declare(strict_types=1);

namespace Alfakher\GrossMargin\Block\Adminhtml\Order\Create;

/**
 * @author af_bv_op
 */
use Magento\Sales\Block\Adminhtml\Order\Create\AbstractCreate;

class PurchaseOrder extends AbstractCreate
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
