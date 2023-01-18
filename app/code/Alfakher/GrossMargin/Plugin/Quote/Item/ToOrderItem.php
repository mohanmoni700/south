<?php
declare(strict_types=1);

namespace Alfakher\GrossMargin\Plugin\Quote\Item;

/**
 * @author af_bv_op
 */
use Alfakher\GrossMargin\ViewModel\GrossMargin;

class ToOrderItem
{
    /**
     * Constructor
     * @param GrossMargin $grossMarginViewModel
     */
    public function __construct(
        GrossMargin $grossMarginViewModel
    ) {
        $this->grossMarginViewModel = $grossMarginViewModel;
    }

    /**
     * Around Convert
     *
     * @param \Magento\Quote\Model\Quote\Item\ToOrderItem $subject
     * @param \Closure $proceed
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @param array $additional
     */
    public function aroundConvert(
        \Magento\Quote\Model\Quote\Item\ToOrderItem $subject,
        \Closure $proceed,
        \Magento\Quote\Model\Quote\Item\AbstractItem $item,
        $additional = []
    ) {
        $orderItem = $proceed($item, $additional);

        $storeId = $item->getQuote()->getStore()->getStoreId();
        $moduleEnable = $this->grossMarginViewModel->isModuleEnabled($storeId);

        if ($moduleEnable) {
            $orderItem->setGrossMargin($item->getGrossMargin());
        }

        return $orderItem;
    }
}
