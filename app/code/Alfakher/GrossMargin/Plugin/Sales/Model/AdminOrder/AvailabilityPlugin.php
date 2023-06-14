<?php

namespace Alfakher\GrossMargin\Plugin\Sales\Model\AdminOrder;

use Magento\Framework\DataObject;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Sales\Block\Adminhtml\Order\Create\Search\Grid\Renderer\Product;

class AvailabilityPlugin
{
    /**
     * @var StockRegistryInterface
     */
    private StockRegistryInterface $stockRegistry;

    /**
     * Construct
     *
     * @param StockRegistryInterface $stockRegistry
     */
    public function __construct(
        StockRegistryInterface $stockRegistry
    )
    {
        $this->stockRegistry = $stockRegistry;
    }

    /**
     * After plugin for Render function
     *
     * @param Product $subject
     * @param  $result
     * @param DataObject $row
     * @return string
     */
    public function afterRender(Product $subject, $result, DataObject $row)
    {
        $productId = $row->getId();
        $qty = $this->getQty($productId);
        if ($qty <= 0) {
            $result .= '</br>' . '<span style="color: red;"><b>' . "QTY Available = 0 " . '</b></span>';
        } else {
            $result .= '</br>' . '<span style="color: green;"><b>' . "QTY Available = " . $qty . '</b></span>';
        }
        return $result;
    }

    /**
     * Return available balance quantity
     *
     * @param int $productId
     */
    public function getQty(int $productId)
    {
        $stockItem = $this->stockRegistry->getStockItem($productId);
        return $stockItem->getQty();
    }
}
