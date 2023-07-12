<?php

namespace Alfakher\AmastyExtended\Plugin\Block\Cart\Item;

use Magento\Checkout\Block\Cart\Item\Renderer;
use Magento\Quote\Model\Quote\Item\AbstractItem;

/**
 * Plugin class to hide delete cart item button only for approved quote items
 */
class RendererPlugin
{
    /**
     * Around plugin
     *
     * @param  Renderer     $subject
     * @param  callable     $proceed
     * @param  AbstractItem $item
     * @return string
     */
    public function aroundGetActions(
        Renderer     $subject,
        callable     $proceed,
        AbstractItem $item
    ): string {
        if ($item->getOptionByCode('amasty_quote_price')) {
            return '';
        }

        return $proceed($item);
    }
}
