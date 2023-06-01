<?php
/**
 * @category  Alfakher
 * @package   Alfakherd_OutOfStockProduct
 */
declare(strict_types=1);
namespace Alfakher\OutOfStockProduct\Plugin\VisualMerchandiser;

use Magento\Framework\Exception\LocalizedException;
use Magento\VisualMerchandiser\Model\Sorting\OutStockBottom as MagentoOutStockBottom;
use Magento\VisualMerchandiser\Model\Resolver\QuantityAndStock;
use Magento\Framework\DB\Select;

/** Plugin to move out of stock order to bottom of the page */
class OutStockBottom
{
    /**
     * @var QuantityAndStock
     */
    private $quantityStockResolver;

    /**
     * @param Sorting\Factory $factory
     * @param QuantityAndStock $quantityAndStockResolver
     * @throws LocalizedException
     */
    public function __construct(QuantityAndStock $quantityAndStockResolver)
    {
        $this->quantityStockResolver = $quantityAndStockResolver;
    }
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
