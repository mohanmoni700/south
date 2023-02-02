<?php
declare(strict_types=1);

namespace HookahShisha\Customization\Controller\Checkout;

use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Checkout\Model\Cart as CustomerCart;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;

/**
 * Remove out of stock product
 */
class RemoveOutofStock extends Action implements HttpPostActionInterface
{
    /**
     * @var CustomerCart
     */
    protected $cart;

    /**
     * @var StockRegistryInterface
     */
    protected $stockRegistry;

    /**
     * Check data
     *
     * @param Context $context
     * @param CustomerCart $cart
     * @param StockRegistryInterface $stockRegistry
     */
    public function __construct(
        Context $context,
        CustomerCart $cart,
        StockRegistryInterface $stockRegistry
    ) {
        $this->stockRegistry = $stockRegistry;
        $this->cart = $cart;
        parent::__construct($context);
    }

    /**
     * Execute remove item
     *
     * @return Json
     */
    public function execute()
    {
        $quote = $this->cart->getQuote();
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData(["message" => "failure"]);
        if (!empty($quote)) {
            foreach ($quote->getAllItems() as $item) {
                $stockItem = $this->stockRegistry->getStockItem($item->getProduct()->getEntityId());
                $isInStock = $stockItem ? $stockItem->getIsInStock() : false;
                if ($isInStock == false) {
                    $this->cart->removeItem($item->getItemId())->save();
                }
                $resultJson->setData([
                    "message" => "success",
                    "value" => __('All out of stock item deleted successfully'),
                ]);
            }
        }
        return $resultJson;
    }
}
