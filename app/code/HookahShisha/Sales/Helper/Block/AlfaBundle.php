<?php
/**
 * @category  HookahShisha
 * @package   HookahShisha_Sales
 * @author    Janis Verins <info@corra.com>
 */

namespace HookahShisha\Sales\Helper\Block;

use Magento\Sales\Model\Order\Item;
use Magento\Sales\Model\Order\Creditmemo\Item as CreditmemoItem;

class AlfaBundle
{
    public const DEFAULT_SHISHA_TITLE = 'Included Premium Shisha Flavor';

    public const DEFAULT_CHARCOAL_TITLE = 'Free Hookah Charcoal';

    /**
     * Returns alfa bundle product options
     *
     * @param Item|CreditmemoItem $item
     * @param array $items
     * @return array
     */
    public function addAlfaBundleOptions($item, array $items): array
    {
        $alfaBundle = $this->getAlfaBundle($item);
        $alfaBundleOptions = [];

        if (!$alfaBundle) {
            return $alfaBundleOptions;
        }

        $shishaSku = $alfaBundle['shisha_sku'] ?? '';
        $charcoalSku = $alfaBundle['charcoal_sku'] ?? '';
        $superPackFlavours = $alfaBundle['super_pack_flavours'] ?? [];
        $shishaItem = $this->getBundleItemBySku($items, $shishaSku);
        $charcoalItem = $this->getBundleItemBySku($items, $charcoalSku);
        $shishaValue = $shishaItem ? $shishaItem->getProduct()->getAttributeText('flavour') : '';
        $charcoalValue = $charcoalItem
            ? $charcoalItem->getName() . ': ' . $charcoalItem->getProduct()->getCharcoalShortDetail()
            : '';
        if ($superPackFlavours) {
            $alfaBundleOptions[] = [
                'label' => __('Flavors'),
                'value' => implode(', ', $superPackFlavours)
            ];
        }

        if ($shishaValue) {
            $alfaBundleOptions[] = [
                'label' => $item->getProduct()->getShishaTitle() ?? self::DEFAULT_SHISHA_TITLE,
                'value' => $shishaValue
            ];
        }

        if ($charcoalValue) {
            $alfaBundleOptions[] = [
                'label' => $item->getProduct()->getCharcoalTitle() ?? self::DEFAULT_CHARCOAL_TITLE,
                'value' => $charcoalValue
            ];
        }

        return $alfaBundleOptions;
    }

    /**
     * Returns decoded alfa bundle
     *
     * @param Item|CreditmemoItem $item
     * @return array
     */
    public function getAlfaBundle($item): array
    {
        $alfaBundle = $item->getAlfaBundle();
        $alfaBundle = json_decode($alfaBundle, true);

        if (!$alfaBundle) {
            return [];
        }

        return $alfaBundle;
    }

    /**
     * Returns order item by sku
     *
     * @param array $orderItems
     * @param string $sku
     * @return Item|null
     */
    public function getBundleItemBySku(array $orderItems, string $sku): ?Item
    {
        if (!$sku) {
            return null;
        }

        foreach ($orderItems as $item) {
            if ($item->getSku() == $sku) {
                return $item;
            }
        }

        return null;
    }

    /**
     * Returns Super Pack items
     *
     * @param Item|CreditmemoItem $item
     * @return array
     */
    public function getSuperPackItems($item): array
    {
        $alfaBundle = $this->getAlfaBundle($item);

        if (empty($alfaBundle)) {
            return [];
        }

        return $alfaBundle['super_pack'] ?? [];
    }

    /**
     * Returns Super Pack item price
     *
     * @param Item|CreditmemoItem $item
     * @param array $items
     * @return int
     */
    public function getSuperPackItemPrice($item, array $items): int
    {
        $price = 0;
        $superPack = $this->getSuperPackItems($item);

        if (empty($superPack)) {
            return $price;
        }

        foreach ($superPack as $item) {
            $superPackItem = $this->getBundleItemBySku($items, $item['variant_sku']);

            if ($superPackItem) {
                $price += $superPackItem->getPrice();
            }
        }

        return $price;
    }
}
