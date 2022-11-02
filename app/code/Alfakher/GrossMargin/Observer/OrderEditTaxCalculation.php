<?php

declare(strict_types=1);

namespace Alfakher\GrossMargin\Observer;

use Avalara\Excise\Helper\Config as ExciseTaxConfig;

class OrderEditTaxCalculation implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var ExciseTaxConfig
     */
    protected $exciseTaxConfig;

    /**
     * @var Config
     */
    protected $taxConfig;
    
    /**
     * @param ExciseTaxConfig $exciseTaxConfig
     * @param \Magento\Tax\Model\Config $taxConfig
     */
    public function __construct(
        ExciseTaxConfig $exciseTaxConfig,
        \Magento\Tax\Model\Config $taxConfig
    ) {
        $this->exciseTaxConfig = $exciseTaxConfig;
        $this->taxConfig  = $taxConfig;
    }

    /**
     * Execute observer
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(
        \Magento\Framework\Event\Observer $observer
    ) {
        try {
            $order = $observer->getEvent()->getOrder();
            $quote = $observer->getEvent()->getQuote();
            
            if ($quote && $quote->getId()) {

                $shippingAddress = $quote->getShippingAddress();
                $storeId = $quote->getStoreId();
                $isAddressTaxable = $this->exciseTaxConfig->isAddressTaxable($shippingAddress, $storeId);

                if ($isAddressTaxable) { 
                    $order->setExciseTaxResponseOrder($quote->getExciseTaxResponseOrder());
                    if (!$quote->getIsMultiShipping()) {
                        $order->setExciseTax($quote->getExciseTax());
                        $order->setSalesTax($quote->getSalesTax());
                        $order->setTaxAmount($quote->getSalesTax() + $quote->getExciseTax() + $order->getShippingTaxAmount());
                    } else {
                        $taxSummary = $this->getTaxSummary($order);
                        $order->setExciseTax($taxSummary[1]);
                        $order->setSalesTax($taxSummary[0]);
                    }
                    foreach ($order->getAllItems() as $item) {
                        $quoteItemId = $item->getQuoteItemId();
                        $quoteItem = $quote->getItemById($quoteItemId);

                        $item->setSalesTax($quoteItem->getSalesTax());
                        $item->setExciseTax($quoteItem->getExciseTax());
                        $item->setTaxAmount($quoteItem->getSalesTax() + $quoteItem->getExciseTax());
                        /* bv_mp; date : 06-09-22; resolving issue of grand total shipping edit; Start */
                        $item->setBaseTaxAmount($quoteItem->getBaseTaxAmount());
                        /* bv_mp; date : 06-09-22; resolving issue of grand total shipping edit; End */
                        $item->setTaxPercent($quoteItem->getTaxPercent());

                        /* bv_op; date : 24-8-22; resolving issue of row subtotal; Start */
                        $item->setPriceInclTax($quoteItem->getPriceInclTax());
                        $item->setBasePriceInclTax($quoteItem->getBasePriceInclTax());

                        $item->setRowTotalInclTax($quoteItem->getRowTotal() + $quoteItem->getSalesTax() + $quoteItem->getExciseTax());
                        $item->setBaseRowTotalInclTax($quoteItem->getBaseRowTotal() + $quoteItem->getSalesTax() + $quoteItem->getExciseTax());
                        /* bv_op; date : 24-8-22; resolving issue of row subtotal; End */
                    }
                } else {
                    $this->clearItemTax($order);
                }
                $order->save();
                $this->calculateGrandTotal($order);
            }
        } catch (\Exception $e) {
            throw $e;
        }
        return $this;
    }

    /**
     * Get Tax amounts
     *
     * @param $order
     * @return array
     */
    private function getTaxSummary($order)
    {
        $salesTax = $exciseTax = 0;
        foreach ($order->getAllItems() as $item) {
            $salesTax += $item->getSalesTax();
            $exciseTax += $item->getExciseTax();
        }
        return [$salesTax, $exciseTax];
    }

    /**
     * Clear Excise Tax amounts
     *
     * @param $order
     * @return void
     */
    private function clearItemTax($order)
    {
        foreach ($order->getAllItems() as $item) {
            $item->setSalesTax(0);
            $item->setExciseTax(0);
        }
    }
    
    /**
     * Calculate order GrandTotal
     *
     * @param $order
     * @return void
     */
    protected function calculateGrandTotal($order)
    {
        if ($this->checkTaxConfiguration()) {
            $grandTotal     = $order->getSubtotal()
                + $order->getTaxAmount()
                + $order->getShippingAmount()
                + $order->calculateMageWorxFeeAmount()
                - abs($order->getDiscountAmount())
                - abs($order->getGiftCardsAmount())
                - abs($order->getCustomerBalanceAmount());
            $baseGrandTotal = $order->getBaseSubtotal()
                + $order->getBaseTaxAmount()
                + $order->getBaseShippingAmount()
                + $order->calculateMageWorxBaseFeeAmount()
                - abs($order->getBaseDiscountAmount())
                - abs($order->getBaseGiftCardsAmount())
                - abs($order->getBaseCustomerBalanceAmount());
        } else {
            $grandTotal     = $order->getSubtotalInclTax()
                + $order->getShippingInclTax()
                + $order->calculateMageWorxFeeAmount()
                - abs($order->getDiscountAmount())
                - abs($order->getGiftCardsAmount())
                - abs($order->getCustomerBalanceAmount());
            $baseGrandTotal = $order->getBaseSubtotalInclTax()
                + $order->getBaseShippingInclTax()
                + $order->calculateMageWorxBaseFeeAmount()
                - abs($order->getBaseDiscountAmount())
                - abs($order->getBaseGiftCardsAmount())
                - abs($order->getBaseCustomerBalanceAmount());
        }

        /* bv_op; date : 1-8-22; resolving issue of incorrect subtotal on price change; Start */
        $order->setSubtotalInclTax($order->getSubtotal() + $order->getExciseTax() + $order->getSalesTax());
        /* bv_op; date : 1-8-22; resolving issue of incorrect subtotal on price change; End */

        $order->setGrandTotal($grandTotal)
             ->setBaseGrandTotal($baseGrandTotal)->save();
    }

    /**
     * @return bool
     */
    public function checkTaxConfiguration(): bool
    {
        $catalogPrices         = $this->taxConfig->priceIncludesTax() ? 1 : 0;
        $shippingPrices        = $this->taxConfig->shippingPriceIncludesTax() ? 1 : 0;
        $applyTaxAfterDiscount = $this->taxConfig->applyTaxAfterDiscount() ? 1 : 0;

        return !$catalogPrices && !$shippingPrices && $applyTaxAfterDiscount;
    }
}