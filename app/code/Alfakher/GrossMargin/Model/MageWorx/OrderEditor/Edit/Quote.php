<?php
declare(strict_types=1);

namespace Alfakher\GrossMargin\Model\MageWorx\OrderEditor\Edit;

/**
 * @author af_bv_op
 */
use MageWorx\OrderEditor\Model\Order as OrderEditorOrderModel;
use Magento\Sales\Model\Order\Item as OriginalOrderItem;
use MageWorx\OrderEditor\Model\Quote\Item as OrderEditorQuoteItem;
use Magento\Framework\Exception\LocalizedException;

class Quote extends \MageWorx\OrderEditor\Model\Edit\Quote
{
    /**
     * Prepare New Quote Items
     *
     * @param array $params
     * @param OrderEditorOrderModel $order
     * @return array
     */
    protected function prepareNewQuoteItems( // NOSONAR
        array $params,
        OrderEditorOrderModel $order
    ): array {
        $quoteItems = [];

        $quote = $this->getQuoteByOrder($order);
        $quote->setAllItemsAreNew(true); // @TODO why?

        // Prevent drop qty to null in case product is out of stock
        $quote->setIsSuperMode(true);
        $quote->setIgnoreOldQty(true);

        foreach ($params as $productId => $options) {
            try {
                $product = $this->prepareProduct($productId, $order->getStore());
                $config = $this->dataObjectFactory->create(['data' => $options]);
                $quoteItem = $quote->addProduct($product, $config);
                if ($product->getTypeId() != 'bundle') {
                    /*bv_op; debug*/
                    $grossMargin = (($product->getPrice() - $product->getCost()) / $product->getPrice() * 100);
                    $quoteItem->setGrossMargin($grossMargin);
                    /*bv_op; debug*/
                }

            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                continue;
            }

            if (!empty($quoteItem)) {
                if (isset($options['bundle_option'])) {
                    $requestedOptions = count(
                        array_filter(
                            array_values($options['bundle_option']),
                            function ($value) {
                                return !empty($value) || $value === 0;
                            }
                        )
                    );

                    $addedOptions = count($quoteItem->getChildren());

                    if ($requestedOptions > $addedOptions) {
                        $quoteItem->setHasError(true);
                        $quoteItem->setMessage(
                            __(
                                'Not all selected products were added some products are currently unavailable.'
                            )
                        );
                    }
                }

                // Need to set quote id when quote is recreated
                $quoteItem->setQuote($quote)
                    ->setQuoteId($quote->getId());
                $this->oeQuoteItemRepository->save($quoteItem);
                foreach ($quoteItem->getChildren() as $child) {
                    $child->setProductType('simple');
                    $this->oeQuoteItemRepository->save($child);
                }
                $quoteItems[] = $quoteItem;
            }
        }

        $quote->collectTotals();

        /**
         * Fix for bundle products price calculation.
         * Without saving quote there was no row totals or base price saved
         * in the quote_item table.
         */
        $this->quoteRepository->save($quote);

        return $quoteItems;
    }

    /**
     * Create New Order Items
     *
     * @param array $params
     * @param OrderEditorOrderModel $order
     * @return array
     */
    public function createNewOrderItems(
        array $params,
        OrderEditorOrderModel $order
    ): array {
        $params = $this->prepareParams($params);
        $quoteItems = $this->prepareNewQuoteItems($params, $order);

        $orderItems = [];
        foreach ($quoteItems as $quoteItem) {
            try {
                $orderItem = $this->quoteItemToOrderItem->convert($quoteItem);
                $orderItem->setItemId($quoteItem->getItemId());
                $orderItem->setAppliedTaxes($quoteItem->getAppliedTaxes());

                /*bv_op; debug*/
                if ($quoteItem->getGrossMargin()) {
                    $orderItem->setData('gross_margin', $quoteItem->getGrossMargin());
                }
                /*bv_op; debug*/

                if ($quoteItem->getProductType() == 'bundle') {
                    $simpleOrderItems = $this->addSimpleItemsForBundle($quoteItem, $orderItem);
                    $orderItem->setChildrenItems($simpleOrderItems);
                }

                if ($quoteItem->getProductType() == 'configurable') {
                    $orderItem->setData('product_options', $quoteItem->getProductOption());
                    $orderItem->setSku($quoteItem->getSku());
                }

                $orderItem->setMessage($quoteItem->getMessage());
                $orderItem->setHasError($quoteItem->getHasError());
                $orderItems[] = $orderItem;
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }

        return $orderItems;
    }

    /**
     * Convert Order Item To QuoteItem
     *
     * @param OriginalOrderItem $orderItem
     * @param string[] $params
     * @param bool $skipItemErrors
     * @return OrderEditorQuoteItem
     * @throws LocalizedException
     */
    public function convertOrderItemToQuoteItem(
        OriginalOrderItem $orderItem,
        array $params,
        bool $skipItemErrors = false
    ): OrderEditorQuoteItem {

        $quoteItemId = (int)$orderItem->getQuoteItemId();

        $quoteItem = $this->oeQuoteItemRepository->getById($quoteItemId);
        $quote     = $this->getQuoteByQuoteItem($quoteItem);
        $quote->setSkipItemErrors($skipItemErrors);

        $quoteItem->setQuote($quote);

        $dataObjectParams = $this->dataObjectFactory->create(['data' => $params]);
        $quoteItem        = $quote->updateItemAdvanced($quoteItem, $dataObjectParams);
        $quoteItem->isDeleted(false);
        if ($quoteItem->getChildren()) {
            foreach ($quoteItem->getChildren() as $childItem) {
                $childItem->isDeleted(false);
            }
        }

        $quote->collectTotals();
        $ExciseTaxResponseOrder = $quote->getExciseTaxResponseOrder();

        $quote = $this->quoteRepository->getById((int)$quote->getId());
        $quote->setExciseTaxResponseOrder($ExciseTaxResponseOrder);
        $this->quoteRepository->save($quote);
        return $quoteItem;
    }
}
