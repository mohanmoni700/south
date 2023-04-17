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
                $invoiceItemExciseTax = $orderItemExciseTax * $item->getQty();
            }

            if ($orderItem->getSalesTax() > 0) {
                $orderItemSalesTax = $orderItem->getSalesTax() / $orderItem->getQtyOrdered();
                $invoiceItemSalesTax = $orderItemSalesTax * $item->getQty();
            }

            $invoiceExciseTax += $invoiceItemExciseTax;
            $invoiceSalesTax += $invoiceItemSalesTax;

            $item->setExciseTax(number_format((float)$invoiceItemExciseTax, 2, '.', ''));
            $item->setSalesTax(number_format((float)$invoiceItemSalesTax, 2, '.', ''));
        }
        // Setting total Excise and salesTax
        $invoice->setExciseTax(number_format((float)$invoiceExciseTax, 2, '.', ''));
        $invoice->setSalesTax(number_format((float)$invoiceSalesTax, 2, '.', ''));
    }
}
