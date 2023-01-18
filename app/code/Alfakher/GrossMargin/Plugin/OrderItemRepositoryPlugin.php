<?php
declare(strict_types=1);

namespace Alfakher\GrossMargin\Plugin;

/**
 * @author af_bv_op
 */
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Api\Data\OrderItemSearchResultInterface;
use Magento\Sales\Api\OrderItemRepositoryInterface;
use Magento\Sales\Api\Data\OrderItemExtensionFactory;

class OrderItemRepositoryPlugin
{
    public const SALES_TAX = 'sales_tax';
    public const EXCISE_TAX = 'excise_tax';

    /**
     * Constructor
     *
     * @param OrderItemExtensionFactory $orderItemExtensionFactory
     */
    public function __construct(
        OrderItemExtensionFactory $orderItemExtensionFactory
    ) {
        $this->orderItemExtensionFactory = $orderItemExtensionFactory;
    }

    /**
     * After Get
     *
     * @param OrderItemRepositoryInterface $subject
     * @param OrderItemInterface $orderItem
     * @return mixed
     */
    public function afterGet(OrderItemRepositoryInterface $subject, OrderItemInterface $orderItem)
    {
        $salesTax = $orderItem->getData(self::SALES_TAX);
        $salesTax = $salesTax ? $salesTax : 0.00;

        $exciseTax = $orderItem->getData(self::EXCISE_TAX);
        $exciseTax = $exciseTax ? $exciseTax : 0.00;

        $extensionAttributes = $orderItem->getExtensionAttributes();
        $extensionAttributes = $extensionAttributes ? $extensionAttributes : $this->orderItemExtensionFactory->create();

        $extensionAttributes->setSalesTax($salesTax);
        $extensionAttributes->setExciseTax($exciseTax);

        $orderItem->setExtensionAttributes($extensionAttributes);

        return $orderItem;
    }

    /**
     * After Get List
     *
     * @param OrderItemRepositoryInterface $subject
     * @param OrderItemSearchResultInterface $searchResult
     * @return mixed
     */
    public function afterGetList(OrderItemRepositoryInterface $subject, OrderItemSearchResultInterface $searchResult)
    {
        $orderItems = $searchResult->getItems();

        foreach ($orderItems as &$item) {
            $salesTax = $item->getData(self::SALES_TAX);
            $salesTax = $salesTax ? $salesTax : 0.00;

            $exciseTax = $item->getData(self::EXCISE_TAX);
            $exciseTax = $exciseTax ? $exciseTax : 0.00;

            $extensionAttributes = $item->getExtensionAttributes();
            $extensionAttributes = $extensionAttributes ?
                $extensionAttributes :
                $this->orderItemExtensionFactory->create();

            $extensionAttributes->setSalesTax($salesTax);
            $extensionAttributes->setExciseTax($exciseTax);

            $item->setExtensionAttributes($extensionAttributes);
        }
        return $searchResult;
    }
}
