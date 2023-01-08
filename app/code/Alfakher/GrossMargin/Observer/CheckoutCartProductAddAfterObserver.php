<?php
namespace Alfakher\GrossMargin\Observer;

/**
 * @author af_bv_op
 */
use Magento\Framework\Event\Observer;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Alfakher\GrossMargin\ViewModel\GrossMargin;
use Magento\Framework\Exception\LocalizedException;

class CheckoutCartProductAddAfterObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var ProductRepositoryInterface
     */
    private ProductRepositoryInterface $proRepo;
    /**
     * @var GrossMargin
     */
    private GrossMargin $grossMarginViewModel;

    /**
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
     * @throws LocalizedException
     */
    public function execute(Observer $observer)
    {
        $item = $observer->getQuoteItem();
        $storeId = $observer->getEvent()->getQuoteItem()->getStoreId();
        $moduleEnable = $this->grossMarginViewModel->isModuleEnabled($storeId);
        if ($moduleEnable) {
            $grossMargin = 0;
            try {
                $cost = $this->proRepo->getById($item->getProduct()->getId())->getCost();
                if ($item->getPrice() > 0) {
                    $grossMargin = ($item->getPrice() - $cost) / $item->getPrice() * 100;
                }
            } catch (\Exception $e) {
                throw new LocalizedException(
                    __('Error with gross margin module')
                );
            }
            $item->setGrossMargin($grossMargin);
        }
    }
}
