<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Avalara\Excise\Observer\Sales;

class OrderInvoicePayBefore implements \Magento\Framework\Event\ObserverInterface
{

    public function __construct(
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->_request = $request;
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
        $invoice = $observer->getEvent()->getInvoice();
        $invoiceItems = $invoice->getItems();
        $orderItems = $invoice->getOrder()->getAllItems();
        $orderItemsArray=[];
        foreach ($orderItems as $orderItem) {
            $orderItemsArray[$orderItem->getItemId()]=$orderItem;
        }
        $invoiceExciseTax = $invoiceSalesTax = 0;
        foreach ($invoiceItems as $item) {
            $orderItem = $orderItemsArray[$item->getOrderItemId()];
            $invoiceItemExciseTax = $invoiceItemSalesTax = 0;
            if ($orderItem->getExciseTax() > 0) {
                $orderItemExciseTax = $orderItem->getExciseTax() / $orderItem->getQtyOrdered();
                $invoiceItemExciseTax = number_format($orderItemExciseTax * $item->getQty(), 2);
            }

            if ($orderItem->getSalesTax() > 0) {
                $orderItemSalesTax = $orderItem->getSalesTax() / $orderItem->getQtyOrdered();
                $invoiceItemSalesTax = number_format($orderItemSalesTax * $item->getQty(), 2);
            }

            $invoiceExciseTax += $invoiceItemExciseTax;
            $invoiceSalesTax += $invoiceItemSalesTax;

            $item->setExciseTax($invoiceItemExciseTax);
            $item->setSalesTax($invoiceItemSalesTax);
        }
        $invoice->setExciseTax($invoiceExciseTax);
        $invoice->setSalesTax($invoiceSalesTax);
    }
}
