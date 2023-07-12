<?php

namespace HookahShisha\SubscribeGraphQl\Plugin\Magento\Bundle\Model\Product;

use HookahShisha\SubscribeGraphQl\Model\Storage;
use Magedelight\Subscribenow\Plugin\Magento\Bundle\Model\Product\Price as Subject;

class Price
{
    private Storage $storage;

    /**
     * @param Storage $storage
     */
    public function __construct(
        Storage $storage
    )
    {
        $this->storage = $storage;
    }

    public function aroundAfterGetSelectionFinalTotalPrice(
        Subject $subject,
        callable $proceed,
        $parentSubject,
        $result,
        $bundleProduct,
        $selectionProduct,
        $bundleQty,
        $selectionQty,
        $multiplyQty = true,
        $takeTierPrice = true
    )
    {
        $discountRate = floatval($this->storage->get('subscribe_order_product_discount_rate'));
        if ($discountRate) {
            $productPrice = $result;
            $discount = $productPrice * $discountRate;
            $finalPrice = $productPrice - $discount;
            return $finalPrice;
        } else {
            return $proceed(
                $parentSubject,
                $result,
                $bundleProduct,
                $selectionProduct,
                $bundleQty,
                $selectionQty,
                $multiplyQty = true,
                $takeTierPrice = true
            );
        }
    }
}
