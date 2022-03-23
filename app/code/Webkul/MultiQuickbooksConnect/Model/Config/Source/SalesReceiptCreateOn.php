<?php
/**
 * Webkul MultiQuickbooksConnect SalesReceiptCreateOn Config Source Model
 * @category  Webkul
 * @package   Webkul_MultiQuickbooksConnect
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited(https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MultiQuickbooksConnect\Model\Config\Source;

class SalesReceiptCreateOn implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Return options array
     *
     * @param int $store
     * @return array
     */
  
    public function toOptionArray($store = null)
    {
        $carriers = [
            ['value'=>'none', 'label'=>__('No Auto Sync')],
            ['value'=>'order_place', 'label'=>__('Order Place')],
            ['value'=>'invoice_create', 'label'=>__('Invoice Create')],
            ['value'=>'order_complete', 'label'=>__('Order Complete')]
        ];
        return $carriers;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $optionList=$this->toOptionArray();
        $optionArray=[];
        foreach ($optionList as $option) {
            $optionArray[$option['value']] = $option['label'];
        }
        return $optionArray;
    }
}
