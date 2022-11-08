<?php

declare (strict_types = 1);

namespace HookahShisha\Customization\Model\Pdf;

class Bundle extends \Magetrend\PdfTemplates\Model\Pdf\Element\Items\Renderer\Bundle
{
    /**
     * Including tax price for bundle product
     *
     * @param \Magetrend\PdfTemplates\Model\Pdf\Element\Items\Renderer\Bundle $subject
     * @param array $result
     * @param array $item
     * @return array
     */
    public function afterGetOrderItemOptions(
        \Magetrend\PdfTemplates\Model\Pdf\Element\Items\Renderer\Bundle $subject,
        $result,
        $item
    ) {

        $bundleOptions = [];

        $item = $subject->getItem();
        if ($item instanceof \Magento\Sales\Model\Order\Item) {
            $options = $item->getProductOptions();
        } else {
            $options = $item->getOrderItem()->getProductOptions();
        }

        $order = $subject->getOrder();
        $allItems = $order->getAllItems();

        $currencyCode = $subject->moduleHelper->getCurrencyCode($item->getStoreId());

        foreach ($allItems as $items) {
            if ($items['parent_item_id'] == $item->getOrderItem()->getItemId()) {
                $price = $subject->moduleHelper->formatPrice($currencyCode, $items['price_incl_tax']);
                $bundleOptions[] = [
                    'label' => $items['name'],
                    'value' => (int) $items['qty_ordered'] . ' x ' . $price,
                ];
            }
        }
        return $bundleOptions;
    }
}
