<?php

namespace Alfakher\GrossMargin\Observer;

/**
 * @author af_bv_op
 */
use Magento\Framework\Event\Observer;

class GrossMarginToOrder implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * Constructor
     *
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $proRepo
     * @param \Alfakher\GrossMargin\ViewModel\GrossMargin $grossMarginViewModel
     */
    public function __construct(
        \Magento\Catalog\Api\ProductRepositoryInterface $proRepo,
        \Alfakher\GrossMargin\ViewModel\GrossMargin $grossMarginViewModel
    ) {
        $this->proRepo = $proRepo;
        $this->grossMarginViewModel = $grossMarginViewModel;
    }

    /**
     * Execute
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $order = $observer->getOrder();
        $websiteId = $order->getStore()->getWebsiteId();
        $moduleEnable = $this->grossMarginViewModel->isModuleEnabled($websiteId);

        if ($moduleEnable) {
            $grossMargin = 0;
            try {
                $items = $order->getAllItems();
                $grandCost = 0;
                foreach ($items as $item) {
                    $grandCost += $item->getProduct()->getCost() * $item->getQtyOrdered();
                }

                $grossMargin = ($order->getSubtotal() - $grandCost) / $order->getSubtotal() * 100;
            } catch (\Exception $e) {
                $grossMargin = 0;
            }
            $order->setGrossMargin($grossMargin)->save();
        }
    }
}
