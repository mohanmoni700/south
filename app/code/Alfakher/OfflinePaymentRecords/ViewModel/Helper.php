<?php

namespace Alfakher\OfflinePaymentRecords\ViewModel;

class Helper implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    public const MODULE_ENABLE = "hookahshisha/af_offline_payment_records/enable";
    public const INVOICE_ENABLE = "hookahshisha/af_offline_payment_records/after_invoice";
    public const ALLOWED_PAYMENT = "hookahshisha/af_offline_payment_records/valid_payment";

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Alfakher\OfflinePaymentRecords\Model\OfflinePaymentRecordFactory $paymentRecords
     * @param \Magento\Directory\Model\Currency $currency
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Alfakher\OfflinePaymentRecords\Model\OfflinePaymentRecordFactory $paymentRecords,
        \Magento\Directory\Model\Currency $currency
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->_paymentRecords = $paymentRecords;
        $this->currency = $currency;
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

    /**
     * Check if allowed for invoiced orders
     *
     * @param int $websiteId
     */
    public function isAllowedForInvoicedOrder($websiteId)
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE;
        return $this->scopeConfig->getValue(self::INVOICE_ENABLE, $storeScope, $websiteId);
    }

    /**
     * Get allowed payment method
     *
     * @param int $websiteId
     */
    public function getAllowedPayment($websiteId)
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE;
        return $this->scopeConfig->getValue(self::ALLOWED_PAYMENT, $storeScope, $websiteId);
    }

    /**
     * Get record collection
     *
     * @param int $orderId
     */
    public function getRecordCollection($orderId)
    {
        return $this->_paymentRecords->create()->getCollection()->addFieldToFilter("order_id", ['eq' => $orderId]);
    }

    /**
     * Get total paid amount
     *
     * @param int $orderId
     */
    public function getTotalPaidAmount($orderId)
    {
        $collection = $this->_paymentRecords->create()->getCollection()
            ->addFieldToFilter("order_id", ['eq' => $orderId]);
        if ($collection->count()) {
            $collectioArr = $collection->getData();
            $totalPaid = array_sum(
                array_map(
                    function ($item) {
                        return $item['amount_paid'];
                    },
                    $collectioArr
                )
            );
            return $totalPaid;
        } else {
            return 0;
        }
    }

    /**
     * Get formatted total due
     *
     * @param float $totalDue
     */
    public function getTotalDue($totalDue)
    {
        return $this->currency->format($totalDue, ['display' => \Zend_Currency::NO_SYMBOL], false);
    }
}
