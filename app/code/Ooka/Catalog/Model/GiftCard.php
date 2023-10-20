<?php

declare(strict_types=1);

namespace Ooka\Catalog\Model;

use Magento\Eav\Model\Config;
use Magento\GiftCard\Model\ResourceModel\Giftcard\Amount;

class GiftCard
{
    const ENTITY_CATALOG_PRODUCT = 'catalog_product';
    private Amount $amount;
    private Config $eavConfig;


    /**
     * @param Amount $amount
     * @param Config $eavConfig
     */
    public function __construct(
        Amount $amount,
        Config $eavConfig
    ) {
        $this->amount = $amount;
        $this->eavConfig = $eavConfig;
    }

    /**
     * @param $product
     * @return array
     */
    public function getGiftCardAmounts($product)
    {
        try {
            $attribute= $this->eavConfig->getAttribute(self::ENTITY_CATALOG_PRODUCT, 'giftcard_amounts');
            return $this->amount->loadProductData($product, $attribute);
        } catch (\Exception $exception) {
            return [];
        }
    }
}
