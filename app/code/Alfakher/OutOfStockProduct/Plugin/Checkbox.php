<?php

namespace Alfakher\OutOfStockProduct\Plugin;

class Checkbox
{
    /**
     * [afterGetTemplate]
     *
     * @param \Magento\Bundle\Block\Adminhtml\Catalog\Product\Composite\Fieldset\Options\Type\Checkbox $subject
     * @param mixed $result
     * @return mixed
     */
    public function afterGetTemplate(
        \Magento\Bundle\Block\Adminhtml\Catalog\Product\Composite\Fieldset\Options\Type\Checkbox $subject,
        $result
    ) {
        return 'Alfakher_OutOfStockProduct::catalog/product/composite/fieldset/options/type/checkbox.phtml';
    }
}
