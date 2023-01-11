<?php
declare(strict_types=1);
namespace Alfakher\SeoUrlPrefix\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use Magento\Store\Api\StoreRepositoryInterface;

class StoreOption implements ArrayInterface
{
    /**
     * @var StoreRepositoryInterface
     */
    protected $storeRepositoryInterface;

   /**
    * Contructor
    *
    * @param StoreRepositoryInterface $storeRepositoryInterface
    */
    public function __construct(
        StoreRepositoryInterface $storeRepositoryInterface
    ) {
        $this->storeRepositoryInterface = $storeRepositoryInterface;
    }

    /**
     * Fetch Store Code
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
        $stores = $this->storeRepositoryInterface->getList();
        foreach ($stores as $store) {
            $id = $store->getId();
            $storeCode = $store->getCode();
            $options[] = [
                'value' => $id,
                'label' => $storeCode,
            ];
        }
        return $options;
    }
}
