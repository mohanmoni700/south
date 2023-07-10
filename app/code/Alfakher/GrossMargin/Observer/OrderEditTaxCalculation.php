<?php
declare(strict_types=1);

namespace Alfakher\GrossMargin\Observer;

use Magento\Tax\Model\Config;
use Avalara\Excise\Helper\Config as ExciseTaxConfig;
use Magento\Framework\Event\Observer;

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
     * @param Config $taxConfig
     */
    public function __construct(
        ExciseTaxConfig $exciseTaxConfig,
        Config $taxConfig
    ) {
        $this->exciseTaxConfig = $exciseTaxConfig;
        $this->taxConfig  = $taxConfig;
    }

    /**
     * Execute observer
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(
        Observer $observer
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
                        if (!is_null($quote->getExciseTax()) && $quote->getExciseTax() > 0) {
                            $order->setExciseTax($quote->getExciseTax());
                        }
                        if (!is_null($quote->getSalesTax()) && $quote->getSalesTax() > 0) {
                            $order->setSalesTax($quote->getSalesTax());
                        }
                        if (!is_null($quote->getExciseTax()) && $quote->getExciseTax() > 0 || !is_null($quote->getSalesTax()) && $quote->getSalesTax() > 0) {
                            $order->setTaxAmount($quote->getSalesTax()
                                + $quote->getExciseTax()
                                + $order->getShippingTaxAmount());
                            $order->setBaseTaxAmount($quote->getSalesTax()
                                + $quote->getExciseTax()
                                + $order->getBaseShippingTaxAmount());
                        }
                    } else {
                        $taxSummary = $this->getTaxSummary($order);
                        $order->setExciseTax($taxSummary[1]);
                        $order->setSalesTax($taxSummary[0]);
                    }
                    
                    foreach ($order->getAllItems() as $item) {
                        $quoteItemId = $item->getQuoteItemId();
                        $quoteItem = $quote->getItemById($quoteItemId);

                        if ($quoteItem) {
                            if (!is_null($quote->getSalesTax()) && $quote->getSalesTax() > 0) {
                                $item->setSalesTax($quoteItem->getSalesTax());
                            }
                            if (!is_null($quote->getExciseTax()) && $quote->getExciseTax() > 0) {
                                $item->setExciseTax($quoteItem->getExciseTax());
                            }
                            if (!is_null($quote->getExciseTax()) && $quote->getExciseTax() > 0 || !is_null($quote->getSalesTax()) && $quote->getSalesTax() > 0) {
                                $item->setTaxAmount($quoteItem->getSalesTax() + $quoteItem->getExciseTax());
                            }

                            /* bv_mp; date : 06-09-22; resolving issue of grand total shipping edit; Start */
                            $item->setBaseTaxAmount($quoteItem->getBaseTaxAmount());
                            /* bv_mp; date : 06-09-22; resolving issue of grand total shipping edit; End */

                            if (!is_null($quote->getTaxPercent()) && $quoteItem->getTaxPercent() > 0) {
                                $item->setTaxPercent($quoteItem->getTaxPercent());
                            }

                            /* bv_op; date : 24-8-22; resolving issue of row subtotal; Start */
                            $item->setPriceInclTax($quoteItem->getPriceInclTax());
                            $item->setBasePriceInclTax($quoteItem->getBasePriceInclTax());

                            $item->setRowTotalInclTax($quoteItem->getRowTotal()
                                + $quoteItem->getSalesTax()
                                + $quoteItem->getExciseTax());
                            $item->setBaseRowTotalInclTax($quoteItem->getBaseRowTotal()
                                + $quoteItem->getSalesTax()
                                + $quoteItem->getExciseTax());
                            /* bv_op; date : 24-8-22; resolving issue of row subtotal; End */
                        }
                    }
                } else {
                    $this->clearItemTax($order);
                }
                $this->calculateGrandTotal($order);
                $order->save();
            }
        } catch (\Exception $e) {
            throw $e;
        }
        return $this;
    }

    /**
     * Get Tax amounts
     *
     * @param mixed $order
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
     * @param mixed $order
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
     * @param mixed $order
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
     * Check Tax Configuration
     *
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
