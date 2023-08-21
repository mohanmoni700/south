<?php


declare(strict_types=1);

namespace Ooka\Catalog\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;

/**
 * Class BundleConfig
 */
class BundleConfig extends AbstractFieldArray
{
    /**
     * Prepare rendering the new field by adding all the needed columns
     */
    protected function _prepareToRender()
    {
        $this->addColumn('sku', ['label' => __('SKU'), 'class' => 'required-entry']);
        $this->addColumn('store_credit', ['label' => __('Store Credit'), 'class' => 'required-entry']);
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }
}
