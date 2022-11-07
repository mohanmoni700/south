<?php

declare(strict_types=1);

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
        foreach ($result as $key => $options) {
            foreach ($options['value'] as $val => $option) {
                $excludePrice = explode(" ", $options['value'][$val]);
                array_pop($excludePrice);
                $result[$key]['value'][$val] = implode(" ", $excludePrice) . "<br>";
            }
        }
        return $result;
    }
}
