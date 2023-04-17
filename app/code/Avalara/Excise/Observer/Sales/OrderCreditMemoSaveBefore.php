<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Avalara\Excise\Observer\Sales;

class OrderCreditMemoSaveBefore implements \Magento\Framework\Event\ObserverInterface
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
        $creditMemo = $observer->getEvent()->getCreditmemo();

        $creditMemoItems = $creditMemo->getItems();
        $orderItems = $creditMemo->getOrder()->getAllItems();
        $orderItemsArray=[];
        foreach ($orderItems as $orderItem) {
            $orderItemsArray[$orderItem->getItemId()]=$orderItem;
        }
        $creditMemoExciseTax = $creditMemoSalesTax = 0;
        foreach ($creditMemoItems as $item) {
            $orderItem = $orderItemsArray[$item->getOrderItemId()];
            $creditMemoItemExciseTax = $creditMemoItemSalesTax = 0;
            if ($orderItem->getExciseTax() > 0) {
                $orderItemExciseTax = $orderItem->getExciseTax() / $orderItem->getQtyOrdered();
                $creditMemoItemExciseTax = $orderItemExciseTax * $item->getQty();
            }

            if ($orderItem->getSalesTax() > 0) {
                $orderItemSalesTax = $orderItem->getSalesTax() / $orderItem->getQtyOrdered();
                $creditMemoItemSalesTax = $orderItemSalesTax * $item->getQty();
            }

            $creditMemoExciseTax += $creditMemoItemExciseTax;
            $creditMemoSalesTax += $creditMemoItemSalesTax;

            $item->setExciseTax(number_format((float)$creditMemoItemExciseTax, 2, '.', ''));
            $item->setSalesTax(number_format((float)$creditMemoItemSalesTax, 2, '.', ''));
        }
        $creditMemo->setExciseTax(number_format((float)$creditMemoExciseTax, 2, '.', ''));
        $creditMemo->setSalesTax(number_format((float)$creditMemoSalesTax, 2, '.', ''));
    }
}
