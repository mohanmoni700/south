<?php
namespace Alfakher\Productpageb2b\Model\Product\Type;

class Grouped extends \Magento\GroupedProduct\Model\Product\Type\Grouped {

	/**
	 * @inheritDoc
	 */
	public function getAssociatedProducts($product) {
		if (!$product->hasData($this->_keyAssociatedProducts)) {
			$associatedProducts = [];

			$this->setSaleableStatus($product);

			$collection = $this->getAssociatedProductCollection(
				$product
			)->addAttributeToSelect(
				['name', 'price', 'special_price', 'special_from_date', 'special_to_date', 'tax_class_id']
			)->addFilterByRequiredOptions()->addStoreFilter(
				$this->getStoreFilter($product)
			)->addAttributeToFilter(
				'status',
				['in' => $this->getStatusFilters($product)]
			);

			$collection->setOrder('name', 'ASC');
			$collection->getSelect()->order('is_salable DESC');

			foreach ($collection as $item) {
				$associatedProducts[] = $item;
			}

			$product->setData($this->_keyAssociatedProducts, $associatedProducts);
		}
		return $product->getData($this->_keyAssociatedProducts);
	}
}
