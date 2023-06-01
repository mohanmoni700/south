<?php
/**
 * @category  Alfakher
 * @package   Alfakherd_OutOfStockProduct
 */
declare(strict_types=1);
namespace Alfakher\OutOfStockProduct\Plugin\VisualMerchandiser;

use Magento\VisualMerchandiser\Model\Sorting\OutStockBottom as MagentoOutStockBottom;
use Magento\Framework\DB\Select;

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
            ->reset(Select::ORDER)
            ->order(['is_in_stock DESC',
                'stock DESC'
            ]);
        return $collection;
    }
}
