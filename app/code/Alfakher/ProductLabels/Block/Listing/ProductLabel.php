<?php

namespace Alfakher\ProductLabels\Block\Listing;

use Mageplaza\ProductLabels\Block\Listing\Label;
use Mageplaza\ProductLabels\Helper\Data;

class ProductLabel extends Label
{
    /**
     * @param array $data
     *
     * @return mixed|string
     */
    public function getProductTooltip($data)
    {
        $ruleId             = $data['rule_id'];
        $productTooltip     = Data::jsonDecode($data['product_tooltip']);
        $listProductTooltip = Data::jsonDecode($data['list_product_tooltip']);
        $rule               = $this->_ruleFactory->create()->load($ruleId);
        $storeCode          = $this->_helperData->getStore()->getCode();

        if ($rule->getSame()) {
            $productTooltip = $this->escapeHtml(isset($productTooltip[$storeCode])
            && $productTooltip[$storeCode] ?
                $productTooltip[$storeCode] :
                (isset($productTooltip['admin']) ? $productTooltip['admin'] : ''));
        } else {
            $productTooltip = $this->escapeHtml(isset($listProductTooltip[$storeCode])
            && $listProductTooltip[$storeCode] ?
                $listProductTooltip[$storeCode] :
                (isset($productTooltip['admin']) ? $listProductTooltip['admin'] : ''));
        }

        return $productTooltip;
    }
}
