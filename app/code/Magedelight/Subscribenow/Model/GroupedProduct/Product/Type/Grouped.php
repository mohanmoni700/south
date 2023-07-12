<?php
namespace Magedelight\Subscribenow\Model\GroupedProduct\Product\Type;

use Magento\GroupedProduct\Model\Product\Type\Grouped as MagentoGrouped;

class Grouped extends MagentoGrouped
{
    /**
     * Prepare product and its configuration to be added to some products list.
     *
     * Perform standard preparation process and add logic specific to Grouped product type.
     *
     * @param \Magento\Framework\DataObject $buyRequest
     * @param \Magento\Catalog\Model\Product $product
     * @param string $processMode
     * @return \Magento\Framework\Phrase|array|string
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _prepareProduct(\Magento\Framework\DataObject $buyRequest, $product, $processMode)
    {
        $products = [];
        $associatedProductsInfo = [];
        $isStrictProcessMode = $this->_isStrictProcessMode($processMode);
        $productsInfo = $this->getProductInfo($buyRequest, $product, $isStrictProcessMode);
        if (is_string($productsInfo)) {
            return $productsInfo;
        }
        $associatedProducts = !$isStrictProcessMode || !empty($productsInfo)
            ? $this->getAssociatedProducts($product)
            : false;

        foreach ($associatedProducts as $subProduct) {
            $qty = $productsInfo[$subProduct->getId()];
            if (!is_numeric($qty) || empty($qty)) {
                continue;
            }

            $_result = $subProduct->getTypeInstance()->_prepareProduct($buyRequest, $subProduct, $processMode);

            if (is_string($_result)) {
                return $_result;
            } elseif (!isset($_result[0])) {
                return __('Cannot process the item.')->render();
            }

            if ($isStrictProcessMode) {
                $_result[0]->setCartQty($qty);
                $_result[0]->addCustomOption('product_type', self::TYPE_CODE, $product);
                $_result[0]->addCustomOption(
                    'info_buyRequest',
                    $this->serializer->serialize(
                        [
                            'super_product_config' => [
                                'product_type' => self::TYPE_CODE,
                                'product_id' => $product->getId(),
                            ],
                        ]
                    )
                );

                $this->_eventManager->dispatch(
                    'subscribenow_grouped_product_option_prepare',
                    ['child_product' => $_result[0], 'buy_request' => $buyRequest, 'product' => $product]
                );
                $products[] = $_result[0];
            } else {
                $associatedProductsInfo[] = [$subProduct->getId() => $qty];
                $product->addCustomOption('associated_product_' . $subProduct->getId(), $qty);
            }
        }

        if (!$isStrictProcessMode || count($associatedProductsInfo)) {
            $product->addCustomOption('product_type', self::TYPE_CODE, $product);
            $product->addCustomOption('info_buyRequest', $this->serializer->serialize($buyRequest->getData()));

            $products[] = $product;
        }

        if (count($products)) {
            return $products;
        }

        return __('Please specify the quantity of product(s).')->render();
    }
}
