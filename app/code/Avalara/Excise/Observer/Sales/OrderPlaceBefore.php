<?php

/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Avalara\Excise\Observer\Sales;

class OrderPlaceBefore implements \Magento\Framework\Event\ObserverInterface
{

    public function __construct(
        \Magento\Quote\Model\QuoteFactory $quoteFactory
    ) {
        $this->quoteFactory = $quoteFactory;
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
            $shippingAddress = $quote->getShippingAddress();
            if ($quote->getIsMultiShipping() && $observer->getEvent()->getAddress()) {
                $shippingAddress = $observer->getEvent()->getAddress();
            }

            if ($quote && $quote->getId()) {
                $order->setExciseTaxResponseOrder($quote->getExciseTaxResponseOrder());
                if (!$quote->getIsMultiShipping()) {
                    $order->setExciseTax($quote->getExciseTax());
                    $order->setSalesTax($quote->getSalesTax());
                } else {
                    $taxSummary = $this->getTaxSummary($order);
                    $order->setExciseTax($taxSummary[1]);
                    $order->setSalesTax($taxSummary[0]);
                }

                //set shipping County.. if shippable product
                if ($shippingAddress && $order->getShippingAddress()) {
                    $quoteShippingCounty =  !empty($shippingAddress->getCounty()) ? $shippingAddress->getCounty() : '';
                    $orderShippingAddress = $order->getShippingAddress();
                    $orderShippingAddress->setCounty($quoteShippingCounty);
                }
                // set billing address County
                $billingAddress = $quote->getBillingAddress();
                $quoteBillingCounty =  !empty($billingAddress->getCounty()) ? $billingAddress->getCounty() : '';
                $orderBillingAddress = $order->getBillingAddress();
                $orderBillingAddress->setCounty($quoteBillingCounty);
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
     * @return void
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
}
