<?php

namespace Alfakher\GrossMargin\Plugin\Sales\Model\AdminOrder;

use Magento\CatalogInventory\Model\Stock\StockItemRepository;

class AvailabilityPlugin
{
    /**
     * @var StockItemRepository
     */
    private StockItemRepository $_stockItemRepository;

    /**
     * construct
     *
     * @param StockItemRepository $stockItemRepository
     */
    public function __construct(
        StockItemRepository $stockItemRepository
    ) {
        $this->_stockItemRepository = $stockItemRepository;
    }
    
    public function afterRender(\Magento\Sales\Block\Adminhtml\Order\Create\Search\Grid\Renderer\Product $subject, $result, \Magento\Framework\DataObject $row)
    {
        $productId = $row->getId();
        $qty = $this->getQty($productId);
        if ($qty == 0) {
            $result .= '<span style="color: red;">' . "Available " . $qty . '</span>';
        } else {
            $result .= '<span style="color: green;">' . "Available " . $qty . '</span>';
        }

        return $result;
    }

    /**
     * Return available balance quantity
     *
     * @param $productId
     */
    public function getQty($productId)
    {
        $stockItem = $this->_stockItemRepository->get($productId);
        return $stockItem->getQty();
    }
}
