<?php
/**
 * @category  HookahShisha
 * @package   HookahShisha_Quote
 * @author    Janis Verins <info@corra.com>
 */

namespace HookahShisha\Quote\Model;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type\AbstractType;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote as SourceQuote;
use Magento\Quote\Model\Quote\Item;

class Quote extends SourceQuote
{
    /**
     * Retrieve quote item by product id or by existing alfa bundle
     *
     * @param Product $product
     * @param null|float|DataObject $request
     * @return false|mixed
     */
    public function getItemByProductOrAlfaBundle(Product $product, $request) // NOSONAR
    {
        $alfaBundle = $request->getAlfaBundle();
        $parentAlfaBundle = $request->getParentAlfaBundle();
        $independentProduct = !$product->getParentProductId();

        // Add alfa bundle base item or return existing one
        if ($alfaBundle && $independentProduct) {
            foreach ($this->getAllItems() as $item) {
                if ($item->getAlfaBundle() == $alfaBundle && $item->getSku() == $product->getSku()) {
                    return $item;
                }
            }

            return false;
        }

        // Add alfa bundle child (simple) item or return existing one
        if ($parentAlfaBundle) {
            foreach ($this->getAllItems() as $item) {
                if ($item->getSku() == $product->getSku() && $item->getParentAlfaBundle() == $parentAlfaBundle) {
                    return $item;
                }
            }

            return false;
        }

        // Add item not associated with alfa bundle or return existing one
        if ($independentProduct) {
            foreach ($this->getAllItems() as $item) {
                if ($item->getSku() == $product->getSku() && !$item->getParentAlfaBundle()) {
                    return $item;
                }
            }

            return false;
        }

        foreach ($this->getAllItems() as $item) {
            if ($item->representProduct($product)) {
                return $item;
            }
        }

        return false;
    }

    /**
     * Add product. Returns error message if product type instance can't prepare product.
     *
     * @param mixed $product
     * @param null|float|DataObject $request
     * @param null|string $processMode
     * @return Item|string
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function addProduct( // NOSONAR
        Product $product,
        $request = null,
        $processMode = AbstractType::PROCESS_MODE_FULL
    ) {
        if ($request === null) {
            $request = 1;
        }
        if (is_numeric($request)) {
            $request = $this->objectFactory->create(['qty' => $request]);
        }
        if (!$request instanceof DataObject) {
            throw new LocalizedException(
                __('We found an invalid request for adding product to quote.')
            );
        }

        if (!$product->isSalable()) {
            throw new LocalizedException(
                __('Product that you are trying to add is not available.')
            );
        }

        $cartCandidates = $product->getTypeInstance()->prepareForCartAdvanced($request, $product, $processMode);

        /**
         * Error message
         */
        if (is_string($cartCandidates) || $cartCandidates instanceof \Magento\Framework\Phrase) {
            return (string)$cartCandidates;
        }

        /**
         * If prepare process return one object
         */
        if (!is_array($cartCandidates)) {
            $cartCandidates = [$cartCandidates];
        }

        $parentItem = null;
        $errors = [];
        $item = null;
        $items = [];
        foreach ($cartCandidates as $candidate) {
            // Child items can be sticked together only within their parent
            $stickWithinParent = $candidate->getParentProductId() ? $parentItem : null;
            $candidate->setStickWithinParent($stickWithinParent);

            $item = $this->getItemByProductOrAlfaBundle($candidate, $request);
            if (!$item) {
                $item = $this->itemProcessor->init($candidate, $request);
                $item->setQuote($this);
                $item->setOptions($candidate->getCustomOptions());
                $item->setProduct($candidate);

                // Set alfa bundle only for configurable type items
                if ($item->getProductType() == 'configurable' && $request->getAlfaBundle()) {
                    $item->setAlfaBundle($request->getAlfaBundle());
                }

                if ($request->getParentAlfaBundle()) {
                    $item->setParentAlfaBundle($request->getParentAlfaBundle());
                    // Included shisha and charcoal products should be charged zero
                    $item->setCustomPrice(0);
                    $item->setOriginalCustomPrice(0);
                }

                // Add only item that is not in quote already
                $this->addItem($item);
            }
            $items[] = $item;

            /**
             * As parent item we should always use the item of first added product
             */
            if (!$parentItem) {
                $parentItem = $item;
            }
            if ($parentItem && $candidate->getParentProductId() && !$item->getParentItem()) {
                $item->setParentItem($parentItem);
            }

            $this->itemProcessor->prepare($item, $request, $candidate);

            // collect errors instead of throwing first one
            if ($item->getHasError()) {
                $this->deleteItem($item);
                foreach ($item->getMessage(false) as $message) {
                    if (!in_array($message, $errors)) {
                        // filter duplicate messages
                        $errors[] = $message;
                    }
                }
                break;
            }
        }
        if (!empty($errors)) {
            throw new LocalizedException(__(implode("\n", $errors)));
        }

        $this->_eventManager->dispatch('sales_quote_product_add_after', ['items' => $items]);
        return $parentItem;
    }
}
