<?php
namespace Alfakher\AddtocartPriceHide\Model;

use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\DataObject\Factory as ObjectFactory;
use Magento\Framework\Locale\FormatInterface;
use Magento\Framework\Math\Division as MathDivision;

/**
 * Provider stocks state
 */
class StockStateProvider extends \Magento\CatalogInventory\Model\StockStateProvider
{
    /**
     * @var MathDivision
     */
    protected $mathDivision;

    /**
     * @var FormatInterface
     */
    protected $localeFormat;

    /**
     * @var ObjectFactory
     */
    protected $objectFactory;

    /**
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * @var bool
     */
    protected $qtyCheckApplicable;

    /**
     * @param MathDivision $mathDivision
     * @param FormatInterface $localeFormat
     * @param ObjectFactory $objectFactory
     * @param ProductFactory $productFactory
     * @param bool $qtyCheckApplicable
     */
    public function __construct(
        MathDivision $mathDivision,
        FormatInterface $localeFormat,
        ObjectFactory $objectFactory,
        ProductFactory $productFactory,
        $qtyCheckApplicable = true
    ) {
        $this->mathDivision = $mathDivision;
        $this->localeFormat = $localeFormat;
        $this->objectFactory = $objectFactory;
        $this->productFactory = $productFactory;
        $this->qtyCheckApplicable = $qtyCheckApplicable;
    }

    /**
     * Validate quote qty
     *
     * @param StockItemInterface $stockItem
     * @param int|float $qty
     * @param int|float $summaryQty
     * @param int|float $origQty
     * @return \Magento\Framework\DataObject
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function checkQuoteItemQty(StockItemInterface $stockItem, $qty, $summaryQty, $origQty = 0)
    {
        $result = $this->objectFactory->create();
        $result->setHasError(false);
        $qty = $this->getNumber($qty);
        $quoteMessage = __('Please correct the quantity for some products.');

        if ($stockItem->getMinSaleQty() && $qty < $stockItem->getMinSaleQty()) {
            $result->setHasError(true)
                ->setMessage(__('The fewest you may purchase is %1.', $stockItem->getMinSaleQty() * 1))
                ->setErrorCode('qty_min')
                ->setQuoteMessage($quoteMessage)
                ->setQuoteMessageIndex('qty');
            return $result;
        }

        if ($stockItem->getMaxSaleQty() && $qty > $stockItem->getMaxSaleQty()) {
            $result->setHasError(true)
                ->setMessage(__('The requested qty exceeds the maximum qty allowed in shopping cart'))
                ->setErrorCode('qty_max')
                ->setQuoteMessage($quoteMessage)
                ->setQuoteMessageIndex('qty');
            return $result;
        }

        $result->addData($this->checkQtyIncrements($stockItem, $qty)->getData());

        $result->setItemIsQtyDecimal($stockItem->getIsQtyDecimal());
        if (!$stockItem->getIsQtyDecimal() && (floor($qty) !== (float) $qty)) {
            $result->setHasError(true)
                ->setMessage(__('You cannot use decimal quantity for this product.'))
                ->setErrorCode('qty_decimal')
                ->setQuoteMessage($quoteMessage)
                ->setQuoteMessageIndex('qty');

            return $result;
        }

        if ($result->getHasError()) {
            return $result;
        }

        if (!$stockItem->getManageStock()) {
            return $result;
        }

        if (!$stockItem->getIsInStock()) {

            $result->setHasError(true)
                ->setErrorCode('out_stock')
                ->setMessage(
                    __(
                        'Product "%1" that you are trying to add is not available.',
                        $stockItem->getProductName()
                    )
                )
                ->setQuoteMessage(__('Some of the products are out of stock.'))
                ->setQuoteMessageIndex('stock');
            $result->setItemUseOldQty(true);
            return $result;
        }

        if (!$this->checkQty($stockItem, $summaryQty) || !$this->checkQty($stockItem, $qty)) {
            $productname = $stockItem->getProductName();
            $message = __('The requested qty is not available. MAX available qty is ' . $stockItem->getQty() . ' for '. $productname);
            $result->setHasError(true)
                ->setErrorCode('qty_available')
                ->setMessage($message)
                ->setQuoteMessage($message)
                ->setQuoteMessageIndex('qty');

            return $result;

        } else {
            if ($stockItem->getQty() - $summaryQty < 0) {
                if ($stockItem->getProductName()) {
                    if ($stockItem->getIsChildItem()) {
                        $backOrderQty = $stockItem->getQty() > 0 ? ($summaryQty - $stockItem->getQty()) * 1 : $qty * 1;
                        if ($backOrderQty > $qty) {
                            $backOrderQty = $qty;
                        }

                        $result->setItemBackorders($backOrderQty);
                    } else {
                        $orderedItems = (int) $stockItem->getOrderedItems();

                        // Available item qty in stock excluding item qty in other quotes
                        $qtyAvailable = ($stockItem->getQty() - ($summaryQty - $qty)) * 1;
                        if ($qtyAvailable > 0) {
                            $backOrderQty = $qty * 1 - $qtyAvailable;
                        } else {
                            $backOrderQty = $qty * 1;
                        }

                        if ($backOrderQty > 0) {
                            $result->setItemBackorders($backOrderQty);
                        }
                        $stockItem->setOrderedItems($orderedItems + $qty);
                    }

                    if ($stockItem->getBackorders() == \Magento\CatalogInventory\Model\Stock::BACKORDERS_YES_NOTIFY) {
                        if (!$stockItem->getIsChildItem()) {
                            $result->setMessage(
                                __(
                                    'We don\'t have as many "%1" as you requested, '
                                    . 'but we\'ll back order the remaining %2.',
                                    $stockItem->getProductName(),
                                    $backOrderQty * 1
                                )
                            );
                        } else {
                            $result->setMessage(
                                __(
                                    'We don\'t have "%1" in the requested quantity, '
                                    . 'so we\'ll back order the remaining %2.',
                                    $stockItem->getProductName(),
                                    $backOrderQty * 1
                                )
                            );
                        }
                    } elseif ($stockItem->getShowDefaultNotificationMessage()) {
                        $result->setMessage(
                            __(
                                'The requested qty is not available for "%1"',
                                $stockItem->getProductName()
                            )
                        );
                    }
                }
            } else {
                if (!$stockItem->getIsChildItem()) {
                    $stockItem->setOrderedItems($qty + (int) $stockItem->getOrderedItems());
                }
            }
        }
        return $result;
    }
}
