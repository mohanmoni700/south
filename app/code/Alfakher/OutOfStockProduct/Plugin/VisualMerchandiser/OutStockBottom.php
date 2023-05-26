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
        $query = (string) $collection->getSelect();
        if (!str_contains($query, "SUM(`at_parent_stock`.`qty`), `at_child_stock`.`qty`) AS `stock`")) {
            $collection = $this->quantityStockResolver->joinStock($collection);
        }
        $collection->getSelect()
            ->reset('order')
            ->order(['is_in_stock DESC',
                'stock DESC'
            ]);
        return $collection;
    }
}
