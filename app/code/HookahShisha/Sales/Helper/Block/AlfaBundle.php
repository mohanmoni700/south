<?php
/**
 * @category  HookahShisha
 * @package   HookahShisha_Sales
 * @author    Janis Verins <info@corra.com>
 */

namespace HookahShisha\Sales\Helper\Block;

use Magento\Sales\Model\Order\Item;

class AlfaBundle
{
    public const DEFAULT_SHISHA_TITLE = 'Included Premium Shisha Flavour';

    public const DEFAULT_CHARCOAL_TITLE = 'Free Hookah Charcoal';

    /**
     * Returns alfa bundle product options
     *
     * @param Item $item
     * @param array $items
     * @return array
     */
    public function addAlfaBundleOptions(Item $item, array $items): array
    {
        $alfaBundle = $this->getAlfaBundle($item);
        $alfaBundleOptions = [];

        if (!$alfaBundle) {
            return $alfaBundleOptions;
        }

        $shishaSku = $alfaBundle['shisha_sku'] ?? '';
        $charcoalSku = $alfaBundle['charcoal_sku'] ?? '';
        $shishaItem = $this->getBundleItemBySku($items, $shishaSku);
        $charcoalItem = $this->getBundleItemBySku($items, $charcoalSku);
        $shishaValue = $shishaItem ? $shishaItem->getProduct()->getAttributeText('flavour') : '';
        $charcoalValue = $charcoalItem
            ? $charcoalItem->getName() . ': ' . $charcoalItem->getProduct()->getCharcoalShortDetail()
            : '';

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
     * @param Item $item
     * @return array
     */
    public function getAlfaBundle(Item $item): array
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
}
