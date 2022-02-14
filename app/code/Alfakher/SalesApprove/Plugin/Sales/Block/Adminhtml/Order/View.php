<?php

namespace Alfakher\SalesApprove\Plugin\Sales\Block\Adminhtml\Order;

/**
 * @author af_bv_op
 */
use Magento\Sales\Block\Adminhtml\Order\View as OrderView;

class View
{
    /**
     * Adding sales approve button to the order view page
     *
     * @param OrderView $subject
     */
    public function beforeSetLayout(OrderView $subject)
    {

        $order = $subject->getOrder();
        $history = $order->getStatusHistoryCollection()->addFieldToFilter('status', ['eq' => 'sales_approved'])->load();

        if (!$history->toArray()['totalRecords']) {

            $subject->addButton(
                'sales_approve_button',
                [
                    'label' => __('Approve Sale'),
                    'class' => __('custom-button primary'),
                    'id' => 'order-view-sales-approve-button',
                    'onclick' => 'setLocation(\'' . $subject->getUrl('salesapprove/order/approve') . '\')',
                ]
            );
        }
    }
}
