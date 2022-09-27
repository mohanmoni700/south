<?php

namespace Alfakher\OutOfStockProduct\Plugin;

class Radio
{
    /**
     * [afterGetTemplate]
     *
     * @param \Magento\Bundle\Block\Adminhtml\Catalog\Product\Composite\Fieldset\Options\Type\Radio $subject
     * @param mixed $result
     * @return mixed
     */
    public function afterGetTemplate(
        \Magento\Bundle\Block\Adminhtml\Catalog\Product\Composite\Fieldset\Options\Type\Radio $subject,
        $result
    ) {
        return 'Alfakher_OutOfStockProduct::catalog/product/composite/fieldset/options/type/radio.phtml';
    }
}
