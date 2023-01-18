<?php
declare(strict_types=1);

namespace Alfakher\GrossMargin\Plugin;

/**
 * @author af_bv_op
 */
use Magento\Sales\Api\Data\OrderExtensionFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderSearchResultInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

class OrderRepositoryPlugin
{

    public const SALES_TAX = 'sales_tax';
    public const EXCISE_TAX = 'excise_tax';

    /**
     * @var $extensionFactory
     */
    protected $extensionFactory;

    /**
     * Constructor
     *
     * @param OrderExtensionFactory $extensionFactory
     */
    public function __construct(OrderExtensionFactory $extensionFactory)
    {
        $this->extensionFactory = $extensionFactory;
    }

    /**
     * After Get
     *
     * @param OrderRepositoryInterface $subject
     * @param OrderInterface $order
     * @return OrderInterface
     */
    public function afterGet(OrderRepositoryInterface $subject, OrderInterface $order)
    {
        $salesTax = $order->getData(self::SALES_TAX);
        $salesTax = $salesTax ? $salesTax : 0.00;

        $exciseTax = $order->getData(self::EXCISE_TAX);
        $exciseTax = $exciseTax ? $exciseTax : 0.00;

        $extensionAttributes = $order->getExtensionAttributes();
        $extensionAttributes = $extensionAttributes ? $extensionAttributes : $this->extensionFactory->create();

        $extensionAttributes->setSalesTax($salesTax);
        $extensionAttributes->setExciseTax($exciseTax);

        $order->setExtensionAttributes($extensionAttributes);

        return $order;
    }

    /**
     * After Get List
     *
     * @param OrderRepositoryInterface $subject
     * @param OrderSearchResultInterface $searchResult
     * @return OrderSearchResultInterface
     */
    public function afterGetList(OrderRepositoryInterface $subject, OrderSearchResultInterface $searchResult)
    {
        $orders = $searchResult->getItems();

        foreach ($orders as &$order) {
            $salesTax = $order->getData(self::SALES_TAX);
            $salesTax = $salesTax ? $salesTax : 0.00;

            $exciseTax = $order->getData(self::EXCISE_TAX);
            $exciseTax = $exciseTax ? $exciseTax : 0.00;

            $extensionAttributes = $order->getExtensionAttributes();
            $extensionAttributes = $extensionAttributes ? $extensionAttributes : $this->extensionFactory->create();

            $extensionAttributes->setSalesTax($salesTax);
            $extensionAttributes->setExciseTax($exciseTax);

            $order->setExtensionAttributes($extensionAttributes);
        }
        return $searchResult;
    }
}
