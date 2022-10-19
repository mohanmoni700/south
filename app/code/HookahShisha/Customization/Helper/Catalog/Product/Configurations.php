<?php

namespace HookahShisha\Customization\Helper\Catalog\Product;

class Configurations
{
    /**
     * Get bundled selections-without price
     *
     * Returns array of options objects.
     * Each option object will contain array of selections objects
     *
     * @param \Magento\Bundle\Helper\Catalog\Product\Configuration $subject
     * @param array $result
     * @return array
     */
    public function afterGetBundleOptions(
        \Magento\Bundle\Helper\Catalog\Product\Configuration $subject,
        array $result
    ) {
        foreach ($result as $key => $option) {
            $excludePrice = explode(" ", $option['value'][0]);
            array_pop($excludePrice);
            $result[$key]['value'][0] = implode(" ", $excludePrice) . "<br>";
        }
        return $result;
    }
}
