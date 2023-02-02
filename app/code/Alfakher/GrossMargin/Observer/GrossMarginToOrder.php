<?php
declare(strict_types=1);

namespace Alfakher\GrossMargin\Observer;

/**
 * @author af_bv_op
 */
use Magento\Framework\Event\Observer;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Alfakher\GrossMargin\ViewModel\GrossMargin;

class GrossMarginToOrder implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * Constructor
     *
     * @param ProductRepositoryInterface $proRepo
     * @param GrossMargin $grossMarginViewModel
     * @return void
     */
    public function __construct(
        ProductRepositoryInterface $proRepo,
        GrossMargin $grossMarginViewModel
    ) {
        $this->proRepo = $proRepo;
        $this->grossMarginViewModel = $grossMarginViewModel;
    }

    /**
     * Execute
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $order = $observer->getOrder();
        $storeId = $order->getStore()->getStoreId();
        $moduleEnable = $this->grossMarginViewModel->isModuleEnabled($storeId);

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
