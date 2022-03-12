<?php

namespace Alfakher\GrossMargin\Plugin\Quote\Item;

/**
 * @author af_bv_op
 */
class ToOrderItem
{
    /**
     * Constructor
     * @param \Alfakher\GrossMargin\ViewModel\GrossMargin $grossMarginViewModel
     */
    public function __construct(
        \Alfakher\GrossMargin\ViewModel\GrossMargin $grossMarginViewModel
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

        $websiteId = $item->getQuote()->getStore()->getWebsiteId();
        $moduleEnable = $this->grossMarginViewModel->isModuleEnabled($websiteId);
        if ($moduleEnable) {
            $orderItem->setGrossMargin($item->getGrossMargin());
        }

        return $orderItem;
    }
}
