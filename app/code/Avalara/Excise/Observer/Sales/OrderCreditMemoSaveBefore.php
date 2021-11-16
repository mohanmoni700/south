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
                $creditMemoItemExciseTax = number_format($orderItemExciseTax * $item->getQty(), 2);
            }

            if ($orderItem->getSalesTax() > 0) {
                $orderItemSalesTax = $orderItem->getSalesTax() / $orderItem->getQtyOrdered();
                $creditMemoItemSalesTax = number_format($orderItemSalesTax * $item->getQty(), 2);
            }

            $creditMemoExciseTax += $creditMemoItemExciseTax;
            $creditMemoSalesTax += $creditMemoItemSalesTax;

            $item->setExciseTax($creditMemoItemExciseTax);
            $item->setSalesTax($creditMemoItemSalesTax);
        }
        $creditMemo->setExciseTax($creditMemoExciseTax);
        $creditMemo->setSalesTax($creditMemoSalesTax);
    }
}
