<?php

namespace Alfakher\OfflinePaymentRecords\ViewModel;

use Alfakher\OfflinePaymentRecords\Model\OfflinePaymentRecordFactory;
use Magento\Directory\Model\Currency;
use Magento\Directory\Model\PriceCurrency;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Helper implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    public const MODULE_ENABLE = "hookahshisha/af_offline_payment_records/enable";
    public const INVOICE_ENABLE = "hookahshisha/af_offline_payment_records/after_invoice";
    public const ALLOWED_PAYMENT = "hookahshisha/af_offline_payment_records/valid_payment";
    
    /**
     * Constructor
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param OfflinePaymentRecordFactory $paymentRecords
     * @param Currency $currency
     * @param PriceCurrency $priceCurrency
     */

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        OfflinePaymentRecordFactory $paymentRecords,
        Currency $currency,
        PriceCurrency $priceCurrency
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->_paymentRecords = $paymentRecords;
        $this->currency = $currency;
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * Check if module is enable
     *
     * @param int $websiteId
     * @return int
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
     * @return int
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
     * @return int
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
     * @return mixed
     */
    public function getRecordCollection($orderId)
    {
        return $this->_paymentRecords->create()->getCollection()->addFieldToFilter("order_id", ['eq' => $orderId]);
    }

    /**
     * Get total paid amount
     *
     * @param int $orderId
     * @return float
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
     * @return float
     */
    public function getTotalDue($totalDue)
    {
        return $this->currency->format($totalDue, ['display' => \Zend_Currency::NO_SYMBOL], false);
    }

    /**
     * Get precise total due
     *
     * @param float $totalDue
     * @return float
     */
    public function getRoundDue($totalDue)
    {
        return $this->priceCurrency->round($totalDue);
    }
}
