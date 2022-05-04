<?php

namespace Alfakher\GrossMargin\ViewModel;

/**
 * View model class
 *
 * @author af_bv_op
 */
class GrossMargin implements \Magento\Framework\View\Element\Block\ArgumentInterface {
	const MODULE_ENABLE = "hookahshisha/gross_margin_group/gross_margin_enable";

	/**
	 * Constructor
	 *
	 * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
	 */
	public function __construct(
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
	) {
		$this->scopeConfig = $scopeConfig;
	}

	/**
	 * Check if module is enable
	 *
	 * @param int $websiteId
	 */
	public function isModuleEnabled($websiteId) {
		$storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE;
		return $this->scopeConfig->getValue(self::MODULE_ENABLE, $storeScope, $websiteId);
	}

	/**
	 * Validate gross margin
	 *
	 * @param \Magento\Sales\Model\Order\Item $item
	 */
	public function validateGrossMargin($item) {
		if ($item->getGrossMargin() <= 0) {
			$cost = 0;
			if ($item->getProduct()) {
				$cost = $item->getProduct()->getCost();
			}
			$price = $item->getPrice();
			try {
				$grossMargin = ($price - $cost) / $price * 100;
				return number_format($grossMargin, 2, ".", "");
			} catch (\Exception $e) {
				return 0.00;
			}
		}
		return $item->getGrossMargin();
	}
}
