<?php

namespace Alfakher\SalesApprove\Plugin\Sales\Block\Adminhtml\Order;

/**
 * @author af_bv_op
 */
use Magento\Sales\Block\Adminhtml\Order\View as OrderView;

class View
{
    const MODULE_ENABLE = "hookahshisha/sales_approve_group/sales_approve_enable";

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Adding sales approve button to the order view page
     *
     * @param OrderView $subject
     */
    public function beforeSetLayout(OrderView $subject)
    {

        $order = $subject->getOrder();
        # $history = $order->getStatusHistoryCollection()->addFieldToFilter('status', ['eq' => 'sales_approved'])->load();
        $history = $order->getStatusHistoryCollection()->addFieldToFilter('comment', ['eq' => 'Sales Approved'])->load();
        $moduleEnable = $this->isModuleEnabled($order->getStore()->getWebsiteId());

        if (!$history->toArray()['totalRecords'] && $moduleEnable) {

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

    /**
     * Check if module is enable
     *
     * @param int $websiteId
     */
    public function isModuleEnabled($websiteId)
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE;
        return $this->scopeConfig->getValue(self::MODULE_ENABLE, $storeScope, $websiteId);
    }
}
