<?php
namespace Alfakher\GrossMargin\Observer;
use Magento\Framework\Event\Observer;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Alfakher\GrossMargin\ViewModel\GrossMargin;

class CheckoutCartProductAddAfterObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * Constructor
     *
     * @param ProductRepositoryInterface $proRepo
     * @param GrossMargin $grossMarginViewModel
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
     */
    public function execute(Observer $observer)
    {
        $item = $observer->getQuoteItem();
        $websiteId = 0;
        if ($item->getQuote()) {
            $websiteId = $item->getQuote()->getStore()->getWebsiteId();
        }
        $moduleEnable = $this->grossMarginViewModel->isModuleEnabled($websiteId);
        if ($moduleEnable) {
            $grossMargin = 0;
            try {
                $cost = $this->proRepo->getById($item->getProduct()->getId())->getCost();
                if ($item->getPrice() > 0) {
                    $grossMargin = ($item->getPrice() - $cost) / $item->getPrice() * 100;
                }
            } catch (\Exception $e) {
                $grossMargin = 0;
            }
            $item->setGrossMargin($grossMargin);
        }
    }
}
