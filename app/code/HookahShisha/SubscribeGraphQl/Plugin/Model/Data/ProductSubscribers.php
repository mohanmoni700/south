<?php

namespace HookahShisha\SubscribeGraphQl\Plugin\Model\Data;

use Magedelight\Subscribenow\Model\Data\ProductSubscribers as Subject;

class ProductSubscribers
{
    public function aroundGetOrderItemInfo(Subject $subject, callable $proceed, $key = null)
    {
        $productId = $subject->getData('product_id');
        $rawData = $subject->getData('additional_info');
        if (!is_array($rawData)) {
            $rawData = \json_decode($rawData, true);
        }

        $data = [
            'item' => $productId
        ];
        $data = array_merge_recursive($data, $this->processBundleOptions($rawData));
        if ($key && is_array($data)) {
            return !empty($data[$key]) ? $data[$key] : null;
        }

        return $data;
    }

    protected function processBundleOptions($data): array
    {
        $bundleOptions = $data['product_options']['bundle_options'] ?? null;
        if (!$bundleOptions) {
            return [];
        }
        $response = [
            'bundle_option' => [],
            'bundle_option_qty' => [],
            'bundle_options_data' => []
        ];

        foreach ($bundleOptions as $bundleOption) {
            $optionId = $bundleOption['option_id'];
            $optionValues = [];
            $optionQty = 0;
            foreach ($bundleOption['value'] as $row) {
                $optionValues[$row['value_id']] = $row['qty'];
                $optionQty += intval($row['qty']);
            }
            $response['bundle_option'][$optionId] = implode(',', array_keys($optionValues));
            $response['bundle_option_qty'][$optionId] = $optionQty;
            $response['bundle_options_data'][$optionId] = $optionValues;
        }

        return $response;
    }
}
