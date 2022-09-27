<?php

namespace Alfakher\OutOfStockProduct\Plugin;

class Select
{
    /**
     * [afterGetTemplate]
     *
     * @param \Magento\Bundle\Block\Adminhtml\Catalog\Product\Composite\Fieldset\Options\Type\Select $subject
     * @param mixed $result
     * @return mixed
     */
    public function afterGetTemplate(
        \Magento\Bundle\Block\Adminhtml\Catalog\Product\Composite\Fieldset\Options\Type\Select $subject,
        $result
    ) {
        return 'Alfakher_OutOfStockProduct::catalog/product/composite/fieldset/options/type/select.phtml';
    }
}
