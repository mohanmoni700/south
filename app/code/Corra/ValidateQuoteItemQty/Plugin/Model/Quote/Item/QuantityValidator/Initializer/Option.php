<?php
declare(strict_types=1);

namespace Corra\ValidateQuoteItemQty\Plugin\Model\Quote\Item\QuantityValidator\Initializer;

use Magento\Bundle\Model\Product\Type;
use Magento\CatalogInventory\Model\Quote\Item\QuantityValidator\Initializer\Option as CatalogInventoryOption;
use Magento\Quote\Model\Quote\Item\Option as QuoteItemOption;
use Magento\Quote\Model\Quote\Item;

class Option
{
    /**
     * @var Type $productType
     */
    protected $productType;

    /**
     * @param Type $productType
     */
    public function __construct(
        Type $productType
    ) {
        $this->productType = $productType;
    }

    /**
     * @param CatalogInventoryOption $subject
     * @param $result
     * @param Item\Option $option
     * @param Item $quoteItem
     * @return mixed
     */
    public function afterInitialize(
        CatalogInventoryOption $subject,
        $result,
        QuoteItemOption $option,
        Item $quoteItem
    ) {
        if ($result->getMessage() !== null) {
            $quoteItem->setIsOutStock(1);
            $message = $result->getMessage()->render();
            $quoteItem->setOosMessage($message);
        }
        if ($quoteItem->getProduct()->getTypeId() == Type::TYPE_CODE) {
            $isSalable = $this->productType->isSalable($quoteItem->getProduct());
            if (!$isSalable) {
                $quoteItem->setIsOutStock(1);
            }
        }
        return $result;
    }
}
