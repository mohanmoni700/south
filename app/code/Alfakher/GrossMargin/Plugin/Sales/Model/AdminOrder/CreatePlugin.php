<?php

namespace Alfakher\GrossMargin\Plugin\Sales\Model\AdminOrder;

/**
 *
 */
class CreatePlugin {

	public function aroundImportPostData(\Magento\Sales\Model\AdminOrder\Create $subject, callable $proceed, $data) {
		$result = $proceed($data);

		if (isset($data['purchase_order'])) {
			$result->getQuote()->addData(['purchase_order' => $data['purchase_order']]);
		}
		return $result;
	}
}