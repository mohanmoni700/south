<?php

namespace Alfakher\Productpageb2b\Plugin;

use Alfakher\Productpageb2b\Helper\Data;

class Option
{

    /**
     * Construct
     *
     * @param Data $helper
     */

    public function __construct(
        Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * Get bundle option price title.
     *
     * @param \Magento\Bundle\Block\Catalog\Product\View\Type\Bundle\Option $subject
     * @param string $result
     * @param Product $selection
     * @param bool $includeContainer
     * @return string
     */

    public function afterGetSelectionQtyTitlePrice(
        \Magento\Bundle\Block\Catalog\Product\View\Type\Bundle\Option $subject,
        $result,
        $selection,
        $includeContainer = true
    ) {
        $subject->setFormatProduct($selection);
        $priceTitle = '<span class="product-name">'
        . $selection->getSelectionQty() * 1
        . ' x '
        . $subject->escapeHtml($selection->getName()) . '</span>';
        return $priceTitle;
    }

    /**
     * Get title price for selection product
     *
     * @param \Magento\Bundle\Block\Catalog\Product\View\Type\Bundle\Option $subject
     * @param string $result
     * @param Product $selection
     * @param bool $includeContainer
     * @return string
     */

    public function afterGetSelectionTitlePrice(
        \Magento\Bundle\Block\Catalog\Product\View\Type\Bundle\Option $subject,
        $result,
        $selection,
        $includeContainer = true
    ) {
        $priceTitle = '<span class="product-name">' . $subject->escapeHtml($selection->getName()) . '</span>';
        return $priceTitle;
    }
}
