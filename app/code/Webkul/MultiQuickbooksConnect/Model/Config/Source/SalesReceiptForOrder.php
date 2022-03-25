<?php
/**
 * Webkul MultiQuickbooksConnect SalesReceiptForOrder Config Source Model
 * @category  Webkul
 * @package   Webkul_MultiQuickbooksConnect
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited(https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MultiQuickbooksConnect\Model\Config\Source;

class SalesReceiptForOrder implements \Magento\Framework\Option\ArrayInterface
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
            ['value'=>'pending', 'label'=>__('Pending')],
            ['value'=>'processing', 'label'=>__('Processing')],
            ['value'=>'complete', 'label'=>__('Complete')]
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
