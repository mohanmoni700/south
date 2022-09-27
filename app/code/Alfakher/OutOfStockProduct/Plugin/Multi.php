<?php

namespace Alfakher\OutOfStockProduct\Plugin;

class Multi
{
    /**
     * [afterGetTemplate]
     *
     * @param \Magento\Bundle\Block\Adminhtml\Catalog\Product\Composite\Fieldset\Options\Type\Multi $subject
     * @param mixed $result
     * @return mixed
     */
    public function afterGetTemplate(
        \Magento\Bundle\Block\Adminhtml\Catalog\Product\Composite\Fieldset\Options\Type\Multi $subject,
        $result
    ) {
        return 'Alfakher_OutOfStockProduct::catalog/product/composite/fieldset/options/type/multi.phtml';
    }
}
