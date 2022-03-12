<?php

namespace Alfakher\GrossMargin\Observer;

/**
 * @author af_bv_op
 */
use Magento\Framework\Event\Observer;

class CheckoutCartProductAddAfterObserver implements \Magento\Framework\Event\ObserverInterface
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
        $item = $observer->getQuoteItem();
        $websiteId = $item->getQuote()->getStore()->getWebsiteId();
        $moduleEnable = $this->grossMarginViewModel->isModuleEnabled($websiteId);

        if ($moduleEnable) {
            $cost = $this->proRepo->getById($item->getProduct()->getId())->getCost();
            $grossMargin = ($item->getPrice() - $cost) / $item->getPrice() * 100;
            $item->setGrossMargin($grossMargin)->save();
        }
    }
}
