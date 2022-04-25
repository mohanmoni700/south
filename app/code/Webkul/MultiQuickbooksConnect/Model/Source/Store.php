<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MultiQuickbooksConnect
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited(https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MultiQuickbooksConnect\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Store\Model\StoreRepository;

/**
 * Webkul ProductAttachment Source Status Model
 */
class Store implements OptionSourceInterface
{
    /**
     * @var Magento\Store\Model\StoreRepository
     */
    protected $storeRepository;

    public function __construct(
        StoreRepository $storeRepository
    ) {
        $this->storeRepository = $storeRepository;
    }
    
    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
        $stores = $this->storeRepository->getList();
        foreach ($stores as $store) {
            $storeId = $store["store_id"];
            $storeName = $store["name"];
            if ($storeId == 0) {
                continue;
            }
            $option =  [
                'label' => $storeName,
                'value' => $storeId
            ];
            $options[] = $option;
        }
        return $options;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toArray()
    {
        $optionList = $this->toOptionArray();
        $optionArray=[];
        foreach ($optionList as $option) {
            $optionArray[$option['value']] = $option['label'];
        }
        return $optionArray;
    }

    /**
     * Get label text of store options
     *
     * @param int $value
     * @return string|bool
     */
    public function getOptionText($value)
    {
        foreach ($this->toOptionArray() as $option) {
            if ($option['value'] == $value) {
                return $option['label'];
            }
        }
        return false;
    }
}
