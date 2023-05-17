<?php
/**
 * @category  Alfakher
 * @package   Alfakherd_Categoryb2b
 * @SuppressWarnings("squid:S1172")
 */
declare(strict_types=1);
namespace Alfakher\Categoryb2b\Plugin\VisualMerchandiser;

use Magento\VisualMerchandiser\Model\Sorting\OutStockBottom as MagentoOutStockBottom;

/** Plugin to move out of stock order to bottom of the page */
class OutStockBottom
{
    /**
     * @param MagentoOutStockBottom $subject
     * @param $collection
     * @return mixed
     */
    public function afterSort(MagentoOutStockBottom $subject, $collection) //NOSONAR $subject is required
    {
        $collection->getSelect()
            ->reset('order')
            ->order(['is_in_stock DESC',
                'stock DESC'
            ]);
        return $collection;
    }
}
